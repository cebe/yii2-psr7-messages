<?php

namespace cebe\psr7\messages;

use Psr\Http\Message\ResponseInterface;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Response extends \yii\web\Response
{
    /**
     * Create a new Yii response from a PSR7 response.
     * @param ResponseInterface $psrResponse
     * @return static
     */
    public static function createFromPsr7Response(ResponseInterface $psrResponse)
    {
        $response = new static();
        $response->statusCode = $psrResponse->getStatusCode();
        $response->statusText = $psrResponse->getReasonPhrase();
        $response->version = $psrResponse->getProtocolVersion();
        foreach($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response->headers->add($name, $value);
            }
        }
        $response->format = self::FORMAT_RAW;
        $response->stream = $psrResponse->getBody()->detach();
        return $response;
    }

    /**
     * Merge this Yii response with properties from PSR7 response.
     *
     * Existing headers will be kept.
     *
     * @param ResponseInterface $psrResponse
     * @return $this
     */
    public function mergeWithPsr7Response(ResponseInterface $psrResponse)
    {
        $this->statusCode = $psrResponse->getStatusCode();
        $this->statusText = $psrResponse->getReasonPhrase();
        $this->version = $psrResponse->getProtocolVersion();
        foreach($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->headers->add($name, $value);
            }
        }

        // TODO - WIP there is some changes needed here in these below lines. access_token API is not sending json body, empty response body with 200 is sent
        $this->format = self::FORMAT_RAW;
        $this->data = $psrResponse->getBody()->__toString();
        $this->content = $this->data;
        $this->stream = $psrResponse->getBody()->detach();
        return $this;
    }
}
