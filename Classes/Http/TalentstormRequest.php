<?php
declare(strict_types=1);

namespace HauerHeinrich\HhTalentstormJobPosts\Http;

/**
 *
 * This file is part of the "hh_talentstorm_job_posts" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Christian Hackl <web@hauer-heinrich.de>, www.Hauer-Heinrich.de
 */

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Http\RequestFactory;

final class TalentstormRequest {

    private RequestFactory $requestFactory;

    private string $apiUrl;

    private string $apiKey;

    // We need the RequestFactory for creating and sending a request,
    // so we inject it into the class using constructor injection.
    public function __construct(RequestFactory $requestFactory) {
        $this->requestFactory = $requestFactory;
    }

    public function setApiUrl(string $apiUrl): void {
        $this->apiUrl = $apiUrl;
    }

    public function setApiKey(string $apiKey): void {
        $this->apiKey = $apiKey;
    }

    /**
     * request
     *
     * @throws \JsonException
     * @throws \RuntimeException
     */
    public function request(): array {
        if(empty($this->apiUrl)) {
            return ['error' => 'no API-URL is set!'];
        }

        // Additional headers for this specific request
        // See: https://docs.guzzlephp.org/en/stable/request-options.html
        $additionalOptions = [
            'headers' => [
                'Cache-Control' => 'no-cache',
                'TS-AUTH-TOKEN' => $this->apiKey
            ],
            'allow_redirects' => false
        ];

        try {
            // Get a PSR-7-compliant response object
            $response = $this->requestFactory->request(
                $this->apiUrl,
                'GET',
                $additionalOptions
            );

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    'Returned status code is ' . $response->getStatusCode()
                );
            }

            if (
                $response->getHeaderLine('Content-Type') !== 'application/json'
                && $response->getHeaderLine('Content-Type') !== 'application/json; charset=utf-8'
                && $response->getHeaderLine('Content-Type') !== 'application/ld+json; charset=utf-8'
            ) {
                throw new \RuntimeException(
                    'The request did not return JSON data'
                );
            }
            // Get the content as a string on a successful request
            $content = $response->getBody()->getContents();

            return json_decode($content, true);
        } catch (\Throwable $th) {
            return ['error' => $th];
        }
    }
}
