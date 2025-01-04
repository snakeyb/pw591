define('custom:views/email-account/record/edit', 'views/email-account/record/edit', function (Dep) {
    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            // Restrict "Fetch Emails" checkbox for non-admins
            if (!this.getUser().isAdmin()) {
                this.setFieldReadOnly('useImap');
            }
        }
    });
});
