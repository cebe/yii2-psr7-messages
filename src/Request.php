<?php

namespace cebe\psr7\messages;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ServerRequestInterface;
use yii\base\InvalidConfigException;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Request extends \yii\web\Request
{
    /** @var string  */
    private $_rawBody;

    /** @var string  */
    private $_protocolVersion;

    /** @var \Psr\Http\Message\UploadedFileInterface[] */
    private $_uploadedFiles;


    /**
     * @return ServerRequestInterface
     * @throws InvalidConfigException
     */
    public function getPsr7Request(): ServerRequestInterface
    {
        if ($this->_rawBody === null) {
            $body = 'php://input';
        } else {
            $stream = fopen('php://memory','rb+');
            fwrite($stream, $this->rawBody);
            rewind($stream);
            $body = new Stream($stream, 'r');
        }

        $request = new ServerRequest(
            $_SERVER,
            $this->getUploadedFiles(),
            new Uri($this->getAbsoluteUrl()),
            $this->getMethod(),
            $body,
            $this->getHeaders()->toArray(),
            $this->getCookies()->toArray(),
            $this->getQueryParams(),
            $this->getBodyParams(),
            $this->getProtocolVersion()
        );

        return $request;
    }

    /**
     * Returns the raw HTTP request body.
     * @return string the request body
     */
    public function getRawBody()
    {
        if ($this->_rawBody === null) {
            $this->_rawBody = file_get_contents('php://input');
        }
        return $this->_rawBody;
    }

    /**
     * Sets the raw HTTP request body, this method is mainly used by test scripts to simulate raw HTTP requests.
     * @param string $rawBody the request body
     */
    public function setRawBody($rawBody)
    {
        $this->_rawBody = $rawBody;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     * @throws InvalidConfigException if protocol version can not be detected.
     */
    public function getProtocolVersion()
    {
        if ($this->_protocolVersion === null) {
            list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);
            if (strncasecmp($protocol, 'http', 4) !== 0) {
                throw new InvalidConfigException('Unable to determine protocol version, protocol does not seem to be HTTP.');
            }
            $this->_protocolVersion = $version;
        }
        return $this->_protocolVersion;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        if ($this->_uploadedFiles === null) {
            $this->_uploadedFiles = [];
            foreach($_FILES as $file) {
                $stream = new Stream(fopen($file['tmp_name'], 'rb'));
                $this->uploadedFiles[] = new UploadedFile(
                    $stream,
                    $file['size'] ?? $stream->getSize(),
                    $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }
        return $this->_uploadedFiles;
    }
}
