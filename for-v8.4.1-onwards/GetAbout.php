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

        // Append user info and host part to the text
        $featureSettingsLoginText = sprintf(
            "# Access to PropertyPipeline Advanced and Ultimate features\n\nIf you have Advanced or Ultimate, access the additional features on the Feature Settings portal [here](https://home.wspp.co.uk)\n\n**If this is the first time you are accessing the Feature Settings Portal, click [here](https://mgmtdocker.wspp.co.uk/webhook/77c267b3-8c5d-4f2b-9af0-e21be80881e8?uid=%s.%s.%s) to have your login details emailed to you.** NB: They are different to your PropertyPipeline login.\n\n",
            htmlspecialchars($firstName),
            htmlspecialchars($lastName),
            htmlspecialchars($hostPart)
        );

        // Prepend the new text to the original text
        $text = $featureSettingsLoginText . $text;

        return ResponseComposer::json([
            'text' => $text,
            'version' => $this->config->get('version'),
        ]);
    }
}
