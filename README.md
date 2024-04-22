# Razorpay Payment Plugin

RazorPay Payment Plugin is a payment gateway plugin integrated with OJS 3. Currently, this plugin supports the OJS version 3.3. 
By using this plugin your journal enables you to receive payment for the below activity :

- Article processing charge
- Subscription
- Issue or Article Download

## Requirement:
- Minimum OJS version 3.3.x or newer
- Your PHP server has the extension installed: PHP-zip, PHP-curl.
- You should have a Razorpay account (currently only provided for Indian business only)

## Installation
- Go to [releases](https://github.com/openjournalteam/razorpay-ojs3-plugin/releases) page.
- Download latest release tar.gz file.
- Upload the tar.gz file in your OJS 3 Plugin Manager
- If you dont know how to upload the plugin please refer to this [documentation](https://docs.pkp.sfu.ca/learning-ojs/3.3/en/settings-website#external-plugins) by PKP

## Configuration
- Open Payment setting in Distribution -> Payments
- Make sure the payment is enabled
- In Payment Plugins field select `RazorPay`
- In RazorPay section, fill the `Key Id` and `Key Secret` that you get from your RazorPay account.
- Save the configuration. 

## License

This plugin is licensed under the GNU GPL v3.0 License. See the [LICENSE](LICENSE) file for more information.

---
Thank you for using our plugin for your journal! If you have any questions or need assistance, please don't hesitate to contact us to support@openjournaltheme.com
