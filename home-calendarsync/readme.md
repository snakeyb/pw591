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
