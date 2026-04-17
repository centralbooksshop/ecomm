<?php
namespace Centralbooks\Freshdesk\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper
{
    protected $curl;
    protected $baseUrl;
    protected $apiKey;
    protected $authToken;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;

        $this->baseUrl = "https://centralbooksonline.freshdesk.com";
        $this->apiKey  = "7TuRjvIAxwslUUo0Fvgi";
        $this->authToken = base64_encode($this->apiKey . ":X");
    }

    private function setHeaders()
    {
        $this->curl->setHeaders([
            "Content-Type"  => "application/json",
            "Authorization" => "Basic " . $this->authToken
        ]);
    }

    private function safeDecode()
    {
        $body = $this->curl->getBody();
        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Return empty array instead of string
            return [];
        }

        // Normalize when API returns single object
        if (is_array($decoded) && isset($decoded['id'])) {
            return [$decoded];
        }

        // Must return array to avoid foreach crashes
        return is_array($decoded) ? $decoded : [];
    }

    private function getStatus()
    {
        return $this->curl->getStatus();
    }

    /* CATEGORY */

    public function getCategoryIdByName($name)
    {
        $this->setHeaders();
        $this->curl->get($this->baseUrl . "/api/v2/solutions/categories");

        $categories = $this->safeDecode();
        foreach ($categories as $category) {
            if (!is_array($category) || empty($category['name'])) {
                continue;
            }

            if (strcasecmp(trim($category['name']), trim($name)) === 0) {
                return $category['id'] ?? false;
            }
        }
        return false;
    }

    public function createCategory($name)
    {
        $this->setHeaders();
        $this->curl->post(
            $this->baseUrl . "/api/v2/solutions/categories",
            json_encode([
                'name' => $name,
                'description' => 'Auto-created category'
            ])
        );

        $response = json_decode($this->curl->getBody(), true);
        return $response['id'] ?? false;
    }

    /* FOLDER */

    public function getFoldersByCategoryId($categoryId)
    {
        $this->setHeaders();
        $this->curl->get(
            $this->baseUrl . "/api/v2/solutions/categories/{$categoryId}/folders"
        );

        return $this->safeDecode();
    }

    public function createFolder($categoryId, $folderName)
	{
		$this->setHeaders();

		$payload = [
			'name'       => $folderName,
			'visibility' => 1
		];

		$this->curl->post(
			$this->baseUrl . "/api/v2/solutions/categories/{$categoryId}/folders",
			json_encode($payload)
		);

		$status   = $this->curl->getStatus();
		$response = json_decode($this->curl->getBody(), true);

		if ($status !== 201) {
			file_put_contents(
				BP . '/var/log/freshdesk_api_error.log',
				"\nFOLDER CREATE FAILED ({$status})\n" .
				"Folder: {$folderName}\n" .
				print_r($response, true),
				FILE_APPEND
			);
			return false;
		}

		return $response['id'] ?? false;
	}


	public function createFolderdup($categoryId, $folderName)
	{
		$this->setHeaders();

		$this->curl->post(
			$this->baseUrl . "/api/v2/solutions/categories/{$categoryId}/folders",
			json_encode([
				'name' => $folderName,
				'visibility' => 1
			])
		);

		$status   = $this->curl->getStatus();
		$response = json_decode($this->curl->getBody(), true);

		if ($status !== 201) {
			// LOG THE ACTUAL ERROR
			file_put_contents(
				BP . '/var/log/freshdesk_api_error.log',
				"\nFOLDER CREATE FAILED ({$status})\n" .
				"Folder: {$folderName}\n" .
				print_r($response, true),
				FILE_APPEND
			);
			return false;
		}

		return $response['id'] ?? false;
	}


    /* ARTICLE */

    public function getArticlesByFolderId($folderId)
    {
        $this->setHeaders();
        $this->curl->get(
            $this->baseUrl . "/api/v2/solutions/folders/{$folderId}/articles"
        );

        return $this->safeDecode();
    }

    public function createArticle($folderId, $payload)
    {
        $this->setHeaders();
        $this->curl->post(
            $this->baseUrl . "/api/v2/solutions/folders/{$folderId}/articles",
            json_encode($payload)
        );

        return json_decode($this->curl->getBody(), true);
    }

    public function updateArticle($articleId, $payload)
    {
        $this->setHeaders();

        $url = $this->baseUrl . "/api/v2/solutions/articles/" . $articleId;

        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($payload));

        // POST is still required
        $this->curl->post($url, json_encode($payload));

        return json_decode($this->curl->getBody(), true);
    }
}
