# MoceanAPI Form Notification Plugin

A simple Grav plugin that allows you to send SMS notification every time someone submits your Grav form.
The **MoceanApi Form Notification** Plugin is for [Grav CMS](http://github.com/getgrav/grav). 

## Installation

Installing the MoceanAPI Form Notification plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install moceanapi-form-notification

This will install the MoceanAPI Form Notification plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/moceanapi-form-notification`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `moceanapi-form-notification`. You can find these files on [GitHub](https://github.com/omar-usman/grav-plugin-moceanapi-form-notification) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/moceanapi-form-notification
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/moceanapi-form-notification/moceanapi-form-notification.yaml` to `user/config/plugins/moceanapi-form-notification.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
api_key: your-moceanapi-key
api_secret: your-moceanapi-secret
enable_notification: true
from: 'From'
to: '60123456789'
message: 'You have received new submission on your {{FORM_NAME}} form.'
enable_auto_response: true
phone_field: 'phone'
auto_response_msg: 'Thank you for your submission.'
```

## Usage

* Create your Grav form. See: [Forms](https://learn.getgrav.org/forms)
* That's it. You should now be able to receive SMS notification automatically.


## Something is wrong?

Make sure you've updated your composer.json file and update your dependencies.

* Add `"guzzlehttp/guzzle": "^6.3"` in your `composer.json` file.
* Run command: `composer update --prefer-dist -vvv --profile`
* Check the plugin `moceanapi-form-notification.yaml` file if you've added the right config values.
