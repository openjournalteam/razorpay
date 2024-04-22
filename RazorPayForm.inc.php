<?php

import('lib.pkp.classes.form.Form');

class RazorPayForm extends Form
{
	var $plugin;

	/** @var QueuedPayment */
	var $queuedPayment;

	/**
	 * @param $plugin
	 * @param $queuedPayment QueuedPayment
	 */
	function __construct($plugin, $queuedPayment)
	{
		$this->plugin = $plugin;
		$this->queuedPayment = $queuedPayment;
		parent::__construct(null);
	}

	/**
	 * @copydoc Form::display()
	 */
	function display($request = null, $template = null)
	{
		// solution for the queuedPayment bug which should be related to the author but instead to the editor
		if ($this->queuedPayment->type == 7) {
			$user = Registry::get('user');
			$this->queuedPayment->userId = $user->getId();
			$queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
			$queuedPaymentDao->updateObject($this->queuedPayment->getId(), $this->queuedPayment);
		}

		$templateMgr = TemplateManager::getManager($request);
		try {
			$amount = (float) $this->queuedPayment->amount * 100;

			$journal = $request->getJournal();
			$api = $this->plugin->getApi();
			$order = $api->order->create([
				'amount' => $amount,
				'currency' => $this->queuedPayment->currencyCode,
			]);

			$templateMgr->assign('key_id', $this->plugin->getSetting($this->plugin->getCurrentContextId(), 'key_id'));
			$templateMgr->assign('amount', $amount);
			$templateMgr->assign('currency', $this->queuedPayment->currencyCode);
			$templateMgr->assign('order_id', $order->id);
			$templateMgr->assign('name', $journal->getLocalizedName());
			$templateMgr->assign('callback_url', $request->url(null, 'payment', 'plugin', array($this->plugin->getName(), 'return'), array('queuedPaymentId' => $this->queuedPayment->getId())));
			$templateMgr->assign('cancel_url', $request->url(null, 'index'));


			$templateMgr->display($this->plugin->getTemplateResource('paymentForm.tpl'));
		} catch (\Throwable $th) {
			error_log('RazorPay transaction exception: ' . $th->getMessage());

			$templateMgr->assign('messageTranslated', 'A transaction error occurred. Please contact the journal manager for details.');
			$templateMgr->display('frontend/pages/message.tpl');
		}
	}
}
