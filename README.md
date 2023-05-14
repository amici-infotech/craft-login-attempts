# Login Attempts plugin for Craft CMS 4

Log all login attempts (failed and success) with error message.

### Requirements
 * PHP version 8.0.0 or higher
 * Craft CMS 4.0 or higher

---
### Installation
Open your terminal and go to your Craft project:

```bash
cd /path/to/project
```
Run this command to load the plugin:

```bash
composer require amici/craft-login-attempts
```

In the Control Panel, go to Settings → Plugins and click the “Install” button for Login Attempts.

---
### Usage
All login attempts will be automatically register to Login Attempts database table. No action needed here. All login attempts can be seen under "Login Attempts" link under CP main menu. Alternatively, Any user's dashboard will have "Login Activity" tab from where we can see all activity (top 100) of that user only.

You can also get user's activity on twig files. Example:

```bash
{% set query = craft.loginAttempts.activity().limit(100).all() %}

{% set query = craft.loginAttempts.activity()
    .userId(currentUser.getId())
    .limit(100).all() %}
```

Supported fields are:
```bash
    id
    userId
    loginName
    loginStatus
    ipAddress
    error
    dateCreated
    dateUpdated
    uid
```

### Documentation
Visit the [Login Attempts](https://docs.amiciinfotech.com/craft-cms/login-attempts) for all documentation, guides, pricing and developer resources.

### Support
Get in touch with us via the [Amici Infotech Support](https://amiciinfotech.com/contact) or by [creating a Github issue](https://github.com/amici-infotech/craft-login-attempts/issues)
