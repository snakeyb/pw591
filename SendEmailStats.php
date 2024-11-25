<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Exceptions\NotFound;

class SendEmailStats extends \Espo\Core\Jobs\Base
{
    public function run()
    {
        $config = $this->getConfig();
        $siteUrl = $config->get('siteUrl'); // Get the site URL of the EspoCRM instance

        // Get the group email accounts
        $emailAccounts = $this->getEntityManager()->getRepository('EmailAccount')->where([
            'type' => 'group'
        ])->find();

        $fromAddresses = [];
        foreach ($emailAccounts as $account) {
            $fromAddresses[] = $account->get('emailAddress');
        }

        if (empty($fromAddresses)) {
            return; // No group email accounts found
        }

$now = new \DateTime();
$lastHourStart = (clone $now)->modify('-1 hour')->setTime(
    (int) $now->format('H') - 1, 0, 0
);
$lastHourEnd = (clone $lastHourStart)->setTime(
    (int) $lastHourStart->format('H'), 59, 59
);

// Query email entity for count of emails sent in the last hour
$emailRepository = $this->getEntityManager()->getRepository('Email');
$count = $emailRepository->where([
    'from' => $fromAddresses,
    'sentDate' => [
        '$gte' => $lastHourStart->format('Y-m-d H:i:s'),
        '$lte' => $lastHourEnd->format('Y-m-d H:i:s'),
    ],
    'status' => 'Sent'
])->count();


        // Prepare JSON payload
        $payload = [
            'siteUrl' => $siteUrl,
            'sentEmailCount' => $count,
            'fromEmailAddresses' => $fromAddresses,
            'timeRange' => [
                'start' => $lastHour->format('Y-m-d H:i:s'),
                'end' => $now->format('Y-m-d H:i:s'),
            ]
        ];

        // Send the payload to another EspoCRM instance
        $apiUrl = 'https://target-espocrm-instance.com/api/v1/custom-endpoint'; // Replace with actual API endpoint
        $apiKey = 'your-api-key'; // Replace with the target API key

        $this->sendPostRequest($apiUrl, $payload, $apiKey);
    }

    private function sendPostRequest($url, $data, $apiKey)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer $apiKey"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->getLogger()->error('CURL error: ' . curl_error($ch));
        } else {
            $this->getLogger()->info('POST request sent. Response: ' . $response);
        }

        curl_close($ch);
    }
}
