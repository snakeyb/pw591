define('custom:views/calendarsync/record/detail', 'views/record/detail', function (Dep) {
    return Dep.extend({
        setup: function () {
            Dep.prototype.setup.call(this);

            this.addButton({
                name: 'customButton',
                label: 'Open URL',
                icon: 'fa-external-link',
                action: this.openUrlAndUpdateRecord.bind(this)
            });
        },

        openUrlAndUpdateRecord: function () {
          
            const externalCalendarType = this.model.get('externalCalendarType');
            if (externalCalendarType === 'Microsoft Exchange') {
                url = this.model.get('msAuthUrl');
            } else if (externalCalendarType === 'Google') {
                url = this.model.get('gAuthUrl');
            } else {
                this.notify('Unsupported calendar type.', 'error');
                return;
            }
          
            if (url) {
                // Open the URL in a new tab
                window.open(url, '_blank');

                // Update a field value
                this.model.set('fieldToUpdate', 'Updated Value'); // Replace 'fieldToUpdate' and 'Updated Value'
                
                // Save the model
                this.model.save()
                    .then(() => {
                        this.notify('Record updated successfully', 'success');
                    })
                    .catch(() => {
                        this.notify('Failed to update the record', 'error');
                    });
            } else {
                this.notify('URL is not defined.', 'error');
            }
        }
    });
});
