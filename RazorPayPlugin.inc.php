<?php

use Razorpay\Api\Api;

import('lib.pkp.classes.plugins.PaymethodPlugin');

class RazorPayPlugin extends PaymethodPlugin
{
	/**
	 * @see Plugin::getDisplayName
	 */
	function getDisplayName()
	{
		return 'RazorPay Payment Plugin';
	}

	/**
	 * @see Plugin::getDescription
	 */
	function getDescription()
	{
		return 'Payments will be processed using the RazorPay service.';
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null)
	{
		if (parent::register($category, $path, $mainContextId)) {
			$this->addLocaleData();
			HookRegistry::register('Form::config::before', array($this, 'addSettings'));
			return true;
		}
		return false;
	}

	/**
	 * Add settings to the payments form
	 *
	 * @param $hookName string
	 * @param $form FormComponent
	 */
	public function addSettings($hookName, $form)
	{
		import('lib.pkp.classes.components.forms.context.PKPPaymentSettingsForm'); // Load constant
		if ($form->id !== FORM_PAYMENT_SETTINGS) {
			return;
		}

		$context = Application::get()->getRequest()->getContext();
		if (!$context) {
			return;
		}
		
		$groupid = 'razorpay';

		$form->addGroup([
			'id' => $groupid,
			'label' => $this->getDisplayName(),
			'showWhen' => 'paymentsEnabled',
		])
			->addField(new \PKP\components\forms\FieldText('key_id', [
				'label' => 'Key Id',
				'value' => $this->getSetting($context->getId(), 'key_id'),
				'groupId' => $groupid,
			]))
			->addField(new \PKP\components\forms\FieldText('key_secret', [
				'label' => 'Key Secret',
				'value' => $this->getSetting($context->getId(), 'key_secret'),
				'groupId' => $groupid,
			]));

		return;
	}

	/**
	 * @copydoc PaymethodPlugin::saveSettings()
	 */
	public function saveSettings($params, $slimRequest, $request)
	{
		$allParams = $slimRequest->getParsedBody();
		$saveParams = [];
		foreach ($allParams as $param => $val) {
			switch ($param) {
				case 'key_id':
				case 'key_secret':
					$saveParams[$param] = (string) $val;
					break;
			}
		}
		$contextId = $request->getContext()->getId();
		foreach ($saveParams as $param => $val) {
			$this->updateSetting($contextId, $param, $val);
		}
		return [];
	}

	/**
	 * @copydoc PaymethodPlugin::getPaymentForm()
	 */
	function getPaymentForm($context, $queuedPayment)
	{
		$this->import('RazorPayForm');
		return new RazorPayForm($this, $queuedPayment);
	}

	/**
	 * @copydoc PaymethodPlugin::isConfigured
	 */
	function isConfigured($context)
	{
		if (!$context) return false;
		if ($this->getSetting($context->getId(), 'key_id') == '') return false;
		if ($this->getSetting($context->getId(), 'key_secret') == '') return false;
		return true;
	}

	function handle($args, $request)
	{
		$journal = $request->getJournal();
		$queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO'); /* @var $queuedPaymentDao QueuedPaymentDAO */
		import('classes.payment.ojs.OJSPaymentManager');
		try {
			$queuedPayment = $queuedPaymentDao->getById($queuedPaymentId = $request->getUserVar('queuedPaymentId'));

			// Prevent errors when users are automatically logged out by OJS by assigning users to the registry
			if (!Validation::isLoggedIn()) {
				// Validation::redirectLogin();
				$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
				$user = $userDao->getById($queuedPayment->getUserId());

				Registry::set('user', $user);
			};
			if (!$queuedPayment) throw new \Exception("Invalid queued payment ID $queuedPaymentId!");

			if ($error  = $request->getUserVar('error')) {
				$description = array_key_exists('description', $error) ? $error['description'] : 'Payment failed';

				throw new \Exception($description);
			}

			$api = $this->getApi();
			$attributes  = array('razorpay_signature'  => $request->getUserVar('razorpay_signature'),  'razorpay_payment_id'  => $request->getUserVar('razorpay_payment_id'), 'razorpay_order_id' => $request->getUserVar('razorpay_order_id'));
			$api->utility->verifyPaymentSignature($attributes);

			$paymentManager = Application::getPaymentManager($journal);
			$paymentManager->fulfillQueuedPayment($request, $queuedPayment, $this->getName());
			$request->redirectUrl($queuedPayment->getRequestUrl());
		} catch (\Throwable $th) {
			error_log('RazorPay transaction exception: ' . $th->getMessage());
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('messageTranslated', 'A transaction error occurred. Please contact the journal manager for details.');
			$templateMgr->display('frontend/pages/message.tpl');
		}
	}

	public function getContextSpecificPluginVersionFile()
	{
		return $this->getPluginPath() . '/version.xml';
	}

	public function getPluginVersion()
	{
		import('lib.pkp.classes.site.VersionCheck');
		$version = VersionCheck::parseVersionXML($this->getContextSpecificPluginVersionFile());

		return $version['release'];
	}

	function getApi()
	{
		return new Api($this->getSetting($this->getCurrentContextId(), 'key_id'), $this->getSetting($this->getCurrentContextId(), 'key_secret'));
	}
}
