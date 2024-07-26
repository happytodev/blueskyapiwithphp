<?php

namespace Happytodev\Blueskyapiwithphp;

use Exception;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Util\Json;

class Blueskyapiwithphp
{

    protected $client;
    protected $headers;
    protected $blueskyBaseUrl;

    public function __construct(string $blueskyApiKey)
    {
        // Init dotenv
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // dd($_ENV);
        $this->blueskyBaseUrl = $_ENV['BLUESKY_BASE_URI'];

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $blueskyApiKey,
        ];

        $this->client = new Client([
            'base_uri' => $this->blueskyBaseUrl,
            'headers' => $this->headers,
        ]);
    }

    /**
     * builUri : build the at-uri based on user handle and postId
     *
     * @param string $handle
     * @param string $postId
     * @return string|Exception
     */
    protected function buildUri($handle, $postId)
    {
        $did = $this->getDid($handle);

        if (is_string($did)) {
            return "at://$did/app.bsky.feed.post/$postId";
        } else {
            throw new Exception($did['error']);
        }
    }

    /**
     * getDid : return the did of the user's handle provided
     *
     * @param string $handle
     * @return string|Exception
     */
    public function getDid($handle)
    {
        $getProfileUri = '/app.bsky.actor.getProfile?actor=';

        try {
            $request = new Request('GET', $this->blueskyBaseUrl . $getProfileUri . $handle, $this->headers);
            $response = $this->client->sendAsync($request)->wait();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody);

            if (isset($responseData->did)) {
                return $responseData->did;
            } else {
                // Handling the case where 'did' is not present in the response
                throw new \Exception('Le champ "did" est manquant dans la réponse de l\'API.');
            }
        } catch (RequestException $e) {
            // Handling Guzzle Exceptions
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
                $responseData = json_decode($responseBody);

                if ($statusCode === 400 && isset($responseData->message)) {
                    return ['error' => $responseData->message];
                } else {
                    return ['error' => 'Une erreur inattendue est survenue.'];
                }
            } else {
                return ['error' => 'Erreur de connexion à l\'API.'];
            }
        } catch (\Exception $e) {
            // Handling others exceptions
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * getPostLikes
     *
     * Return like's infos on a post of an user
     *
     * @param string $handle the handle of post's user
     * @param string $postId the post id
     * @return object The like's information
     */
    public function getPostLikes($handle, $postId)
    {
        $getLikesUri = $this->blueskyBaseUrl . '/app.bsky.feed.getLikes?uri=';

        $atUri = $this->buildUri($handle, $postId);

        $request = new Request('GET', $getLikesUri . $atUri, $this->headers);

        $response = $this->client->sendAsync($request)->wait();

        $responseBody = $response->getBody()->getContents();

        $responseDatas = json_decode($responseBody, true);

        return $responseDatas['likes'] ?? [];
    }


    /**
     * getPostLikesCount
     *
     * Gives the count of likes on a post
     *
     * @param string $handle
     * @param string $postId
     * @return string
     */
    public function getPostLikesCount(string $handle, string $postId): int
    {
        $responseDatas = $this->getPostThread($handle, $postId);

        return $responseDatas['thread']['post']['likeCount'];
    }

    /**
     * getPostRepostsCount
     *
     * Gives the count of repost on a post
     *
     * @param string $handle
     * @param string $postId
     * @return integer
     */
    public function getPostRepostsCount(string $handle, string $postId): int
    {
        $responseDatas = $this->getPostThread($handle, $postId);

        return $responseDatas['thread']['post']['repostCount'];
    }

    /**
     * getPostRepliesCount
     *
     * Gives the count of replies on a post
     *
     * @param string $handle
     * @param string $postId
     * @return string
     */
    public function getPostRepliesCount(string $handle, string $postId): int
    {
        $responseDatas = $this->getPostThread($handle, $postId);

        return $responseDatas['thread']['post']['replyCount'];
    }

    /**
     * getPostThread
     *
     * Gives all information on a post / thread
     *
     * @param string $handle
     * @param string $postId
     * @return string
     */
    public function getPostThread(string $handle, string $postId): array|int|string
    {
        $apiMethod = $this->blueskyBaseUrl . "/app.bsky.feed.getPostThread";

        $atUri = $this->buildUri($handle, $postId);

        $apiParams = "?uri=" . $atUri;

        $request = new Request('GET', $apiMethod . $apiParams, $this->headers);

        $response = $this->client->sendAsync($request)->wait();

        $responseBody = $response->getBody()->getContents();

        return json_decode($responseBody, true);
    }
}
