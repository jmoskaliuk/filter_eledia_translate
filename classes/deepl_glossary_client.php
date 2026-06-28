<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_translations;

use curl;

/**
 * Small DeepL v3 glossary API client.
 *
 * @package filter_translations
 */
class deepl_glossary_client {
    /** @var \stdClass */
    private $config;

    /** @var string */
    private $baseurl;

    /**
     * Constructor.
     *
     * @param \stdClass|null $config
     */
    public function __construct(?\stdClass $config = null) {
        $this->config = $config ?? get_config('filter_translations');
        $this->baseurl = $this->build_baseurl();
    }

    /**
     * Whether the API is ready to call.
     *
     * @return bool
     */
    public function is_configured(): bool {
        return !empty($this->config->deepl_apikey);
    }

    /**
     * Create a glossary with one dictionary.
     *
     * @param string $name
     * @param string $sourcelanguage
     * @param string $targetlanguage
     * @param string $entries
     * @return array
     */
    public function create_glossary(string $name, string $sourcelanguage, string $targetlanguage, string $entries): array {
        return $this->request('post', '/v3/glossaries', [
            'name' => $name,
            'dictionaries' => [[
                'source_lang' => $sourcelanguage,
                'target_lang' => $targetlanguage,
                'entries' => $entries,
                'entries_format' => 'tsv',
            ]],
        ]);
    }

    /**
     * Replace a dictionary within an existing glossary.
     *
     * @param string $glossaryid
     * @param string $sourcelanguage
     * @param string $targetlanguage
     * @param string $entries
     * @return array
     */
    public function replace_dictionary(string $glossaryid, string $sourcelanguage, string $targetlanguage, string $entries): array {
        return $this->request('put', '/v3/glossaries/' . rawurlencode($glossaryid) . '/dictionaries', [
            'source_lang' => $sourcelanguage,
            'target_lang' => $targetlanguage,
            'entries' => $entries,
            'entries_format' => 'tsv',
        ]);
    }

    /**
     * Execute an API request.
     *
     * @param string $method
     * @param string $path
     * @param array|null $payload
     * @return array
     */
    private function request(string $method, string $path, ?array $payload = null): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        if (!$this->is_configured()) {
            throw new \moodle_exception('deeplnotconfigured', 'filter_translations');
        }

        $curl = new curl();
        $curl->setHeader([
            'Authorization: DeepL-Auth-Key ' . $this->config->deepl_apikey,
            'Content-Type: application/json',
        ]);

        $url = $this->baseurl . $path;
        $body = $payload === null ? null : json_encode($payload);
        $response = $method === 'put' ? $curl->put($url, $body) : $curl->post($url, $body);
        $info = $curl->get_info();
        $httpcode = (int)($info['http_code'] ?? 0);

        if ($httpcode < 200 || $httpcode >= 300) {
            $message = 'HTTP ' . ($httpcode ?: 'unknown');
            $decoded = json_decode($response, true);
            if (!empty($decoded['message'])) {
                $message .= ': ' . $decoded['message'];
            }
            if (!empty($decoded['detail'])) {
                $message .= ' ' . $decoded['detail'];
            }
            throw new \moodle_exception('deeplglossarysyncfailed', 'filter_translations', '', $message);
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Build the API base URL from the configured translate endpoint.
     *
     * @return string
     */
    private function build_baseurl(): string {
        $endpoint = trim((string)($this->config->deepl_apiendpoint ?? ''));
        if ($endpoint === '') {
            return 'https://api-free.deepl.com';
        }

        $parts = parse_url($endpoint);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return 'https://api-free.deepl.com';
        }

        return $parts['scheme'] . '://' . $parts['host'];
    }
}
