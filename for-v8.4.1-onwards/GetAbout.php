<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM – Open Source CRM application.
 * Copyright (C) 2014-2024 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Tools\App\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Resource\FileReader;
use Espo\Entities\User;

/**
 * @noinspection PhpUnused
 */
class GetAbout implements Action
{
    public function __construct(
        private FileReader $fileReader,
        private Config $config,
        private User $currentUser
    ) {}

    public function process(Request $request): Response
    {
        $text = $this->fileReader->read('texts/about.md', FileReader\Params::create());

        // Get the logged-in user's details
        // Get the logged-in user's details
        $firstName = $this->currentUser->get('firstName');
        $lastName = $this->currentUser->get('lastName');

        // Get the host part of the site URL
        $siteUrl = $this->config->get('siteUrl');
        $host = parse_url($siteUrl, PHP_URL_HOST);
        $hostPart = explode('.', $host)[0]; // Get the first part (e.g., 'demo' from 'demo.wspp.co.uk')

	// Create a short-lived, one-time assertion for the logged-in user. The signing
// secret stays on the PropertyPipeline server and is never sent to the browser.
$mcpSecretBase64 = (string) $this->config->get('mcpAuthorizationSecret');
$mcpSecret = base64_decode($mcpSecretBase64, true);
$currentUserId = (string) $this->currentUser->getId();
$canonicalHost = strtolower(rtrim((string) $host, '.'));

$mcpAuthorizationText = "## Connect an AI assistant\n\nMCP authorization is not configured for this instance. Contact PropertyPipeline support.\n\n";

if ($mcpSecret !== false && strlen($mcpSecret) === 32 && $currentUserId !== '' && $canonicalHost !== '') {
    $base64UrlEncode = static function (string $value): string {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    };

    $issuedAt = time();
    $payload = [
        'v' => 1,
        'iss' => $canonicalHost,
        'sub' => $currentUserId,
        'aud' => 'propertypipeline-mcp',
        'iat' => $issuedAt,
        'exp' => $issuedAt + 300,
        'jti' => $base64UrlEncode(random_bytes(18)),
    ];

    $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $payloadEncoded = $base64UrlEncode($payloadJson);
    $signature = hash_hmac('sha256', $payloadEncoded, $mcpSecret, true);
    $assertion = $payloadEncoded . '.' . $base64UrlEncode($signature);
    $authorizationUrl = 'https://pp-mcp.nick-osborn.workers.dev/authorize/pp-confirm#assertion=' . $assertion;

    $mcpAuthorizationText = sprintf(
        "## Connect an AI assistant\n\nTo approve a secure connection using your current PropertyPipeline access, [click here](%s). This link expires in five minutes and works once.\n\n",
        $authorizationUrl
    );
}



        // Append user info and host part to the text
        $featureSettingsLoginText = sprintf(
            "# Access to PropertyPipeline Advanced and Ultimate features\n\nIf you have Advanced or Ultimate, access the additional features on the Feature Settings portal [here](https://home.wspp.co.uk)\n\n**If this is the first time you are accessing the Feature Settings Portal, click [here](https://mgmtdocker.wspp.co.uk/webhook/77c267b3-8c5d-4f2b-9af0-e21be80881e8?uid=%s.%s.%s) to have your login details emailed to you.** NB: They are different to your PropertyPipeline login.\n\n",
            htmlspecialchars($firstName),
            htmlspecialchars($lastName),
            htmlspecialchars($hostPart)
        );

        // Prepend the new text to the original text
        $text = $mcpAuthorizationText . $featureSettingsLoginText . $text;

        return ResponseComposer::json([
            'text' => $text,
            'version' => $this->config->get('version'),
        ]);
    }
}
