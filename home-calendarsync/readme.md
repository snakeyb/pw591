NB: This doesnt currently work, it throws an error about a missing button.js, apparently you can get them in the advanced pack.

Add the following to c/var/www/html/custom/Espo/Custom/Resources/metadata/clientDefs# nano CalendarSync.json
{
  "viewDefs": {
    "recordDetail": "custom:views/calendarsync/record/detail"
  }
}


Added the following to:
/var/www/html/custom/Espo/Custom/Resources/i18n/en_GB/CalendarSync.json
    },
    "labels": {
      "Click here to authenticate": "Click here to authenticate"
    }


Create the detail.js file here:
/var/www/html/client/src/views/calendarsync/record/detail.js

Manually update the layout to add a button:
/var/www/html/custom/Espo/Custom/Resources/layouts/CalendarSync/detail.json

            [
                {"type": "button", "name": "calSyncOpenUrl", "label": "Click here to authenticate"}
            ]

