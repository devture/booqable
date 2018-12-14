<?php
namespace Devture\Component\Booqable;

use function GuzzleHttp\Psr7\build_query;

class Communicator {

	private $guzzleClient;
	private $baseUrl;
	private $apiKey;

	public function __construct(\GuzzleHttp\Client $guzzleClient, string $baseUrl, string $apiKey) {
		$this->guzzleClient = $guzzleClient;
		$this->baseUrl = $baseUrl;
		$this->apiKey = $apiKey;
	}

	public function createRequest(string $relativeUrl, array $queryParams): \GuzzleHttp\Psr7\Request {
		return new \GuzzleHttp\Psr7\Request('GET', $this->createFullUrl($relativeUrl, $queryParams));
	}

	public function get(string $relativeUrl, array $queryParams): RawApiResponse {
		$request = new \GuzzleHttp\Psr7\Request('GET', $this->createFullUrl($relativeUrl, $queryParams));
		return $this->sendRequest($request);
	}

	public function put(string $relativeUrl, array $queryParams, array $bodyParams): RawApiResponse {
		$request = new \GuzzleHttp\Psr7\Request('PUT', $this->createFullUrl($relativeUrl, $queryParams));
		$request = $request->withHeader('Content-Type', 'application/json');
		$request = $request->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($bodyParams)));
		return $this->sendRequest($request);
	}

	public function post(string $relativeUrl, array $queryParams, array $bodyParams): RawApiResponse {
		$request = new \GuzzleHttp\Psr7\Request('POST', $this->createFullUrl($relativeUrl, $queryParams));
		$request = $request->withHeader('Content-Type', 'application/json');
		$request = $request->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($bodyParams)));
		return $this->sendRequest($request);
	}

	public function delete(string $relativeUrl, array $queryParams, array $bodyParams): RawApiResponse {
		$request = new \GuzzleHttp\Psr7\Request('DELETE', $this->createFullUrl($relativeUrl, $queryParams));
		$request = $request->withHeader('Content-Type', 'application/json');
		$request = $request->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($bodyParams)));
		return $this->sendRequest($request);
	}

	/**
	 * @throws Exception
	 * @throws Exception\AuthFailure
	 * @throws Exception\BadResponse
	 * @throws \Exception
	 * @return RawApiResponse[]
	 */
	public function executeAll(\Closure $requestSpawner, int $concurency): array {
		$responses = [];

		$pool = new \GuzzleHttp\Pool($this->guzzleClient, $requestSpawner(), [
			'concurency' => $concurency,

			'fulfilled' => function (\GuzzleHttp\Psr7\Response $response, int $index) use (&$responses) {
				// This may throw Exception\BadResponse
				$responses[$index] = $this->processHttpResponse($response);
			},

			'rejected' => function (\Exception $e, int $index) {
				if ($e instanceof \GuzzleHttp\Exception\ClientException) {
					$response = $e->getResponse();

					if ($e->getResponse() !== null) {
						if ($e->getResponse()->getStatusCode() === 401) {
							throw new Exception\AuthFailure('Not authenticated', 0, $e);
						}

						if ($e->getResponse()->getStatusCode() === 404) {
							throw new Exception\NotFound('Not found', 0, $e);
						}
					}

					throw new Exception($e->getMessage(), 0, $e);
				}

				if ($e instanceof \GuzzleHttp\Exception\TransferException) {
					throw new Exception($e->getMessage(), 0, $e);
				}

				throw $e;
			}
		]);

		$promise = $pool->promise();

		$promise->wait();

		ksort($responses);

		$responses = array_values($responses);

		return $responses;
	}

	/**
	 * @throws Exception
	 * @throws Exception\AuthFailure
	 * @throws Exception\BadResponse
	 */
	private function sendRequest(\GuzzleHttp\Psr7\Request $request): RawApiResponse {
		$request = $request->withHeader('Accept', 'application/json');

		try {
			$response = $this->guzzleClient->send($request, ['timeout' => 25]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$response = $e->getResponse();

			if ($e->getResponse() !== null) {
				if ($e->getResponse()->getStatusCode() === 401) {
					throw new Exception\AuthFailure('Not authenticated', 0, $e);
				}

				if ($e->getResponse()->getStatusCode() === 404) {
					throw new Exception\NotFound('Not found', 0, $e);
				}
			}

			throw new Exception($e->getMessage(), 0, $e);
		} catch (\GuzzleHttp\Exception\TransferException $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}

		return $this->processHttpResponse($response);
	}

	private function sendRequestAsync(\GuzzleHttp\Psr7\Request $request): \GuzzleHttp\Promise\Promise {
		$request = $request->withHeader('Accept', 'application/json');

		return $this->guzzleClient->sendAsync($request, ['timeout' => 25]);
	}

	/**
	 * @throws Exception\BadResponse
	 */
	private function processHttpResponse(\GuzzleHttp\Psr7\Response $response): RawApiResponse {
		$responseBody = $response->getBody();
		$data = json_decode($responseBody, true);

		if (json_last_error()) {
			throw new Exception\BadResponse(sprintf(
				'Failed parsing response data as JSON (%s): %s',
				json_last_error_msg(),
				$responseBody
			));
		}

		return new RawApiResponse($data);
	}

	private function createFullUrl(string $relativeUrl, array $queryParams): string {
		$queryParams['api_key'] = $this->apiKey;

		$fullUrl = sprintf('%s/%s', rtrim($this->baseUrl, '/'), ltrim($relativeUrl, '/'));

		if (count($queryParams) > 0) {
			$fullUrl .= '?' . build_query($queryParams);
		}

		return $fullUrl;
	}

}
