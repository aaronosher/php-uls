<?php
namespace VATUSA\Uls;

use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Checker\InvalidClaimException;
use Namshi\JOSE\Base64\Base64Encoder;
use Psr\Log\LoggerInterface;

class Uls
{
    private $_minversion = 2;
    protected $version = 2;
    protected $jwk = [];
    private $token;
    private $logger;

    public function __construct($options = ["version" => 2, "jwk" => [], "facility" => "ZZZ"], LoggerInterface $logger)
    {
        if (isset($logger)) {
            $this->logger = $logger;
        }
        if (isset($options["version"])) {
            $this->version = $options["version"];
        }
        if ($this->version < $this->_minversion) {
            $this->version = $this->_minversion;
            $this->log("VATUSA\Uls: Version was set below minimum version. Assuming version of $this->_minversion instead of " . $options['version'], "notice");
        }
        if (!isset($options["jwk"])) {
            throw new \Exception("jwk option must be set.");
        } else {
            $this->jwk = $options["jwk"];
        }
        if (!isset($options["facility"])) {
            throw new \Exception("facility option must be set.");
        } else {
            $this->facility = $options["facility"];
        }
    }

    private function log($message="", $level="info") {
        if(!isset($this->logger)) {
            return false;
        }

        switch ($level) {
            case "emergency":
                $this->logger->emergency($message);
                break;

            case "alert":
                $this->logger->alert($message);
                break;

            case "warning":
                $this->logger->warning($message);
                break;

            case "notice":
                $this->logger->notice($message);
                break;

            case "info":
                $this->logger->info($message);
                break;

            case "debug":
                $this->logger->debug($message);
                break;

            default:
                $this->logger->log($level, $message);
        }
        return true;
    }

    public function buildUrl($location, $queryString = "")
    {
        $base = "https://login.vatusa.net/uls/";
        $base .= ($this->version == 2) ? "v2/" : "";

        $url = "";

        switch ($location) {
            case "login":
                $url = $base . "login?fac=$this->facility" . (($queryString) ? "&$queryString" : "");
                break;
            case "info":
                $url = $base . "info?token=$this->token";
                break;
            default:
                throw new \Exception("Invalid location");
        }

        return $url;
    }

    public function setJwk($jwk)
    {
        $new_jwk = json_decode($jwk, true);
        if (!$new_jwk) {
            throw new \InvalidArgumentException("Invalid JWK");
        }
    }

    public function redirectUrl($dev = false)
    {
        return $this->buildUrl('login', ($dev) ? "dev" : null);
    }

    public function verifyToken($token)
    {
        if (!$token) {
            return false;
        }
        if (empty($this->jwk)) {
            throw new \Exception("Invalid JWK");
        }

        // Support all algorithms for future growth
        $algorithmManager = AlgorithmManager::create([
            new HS256(), new HS384(), new HS512()
        ]);

        $jwsVerifier = new JWSVerifier($algorithmManager);
        $jwk = JWK::create($this->jwk);
        $jsonConverter = new StandardConverter();
        $serializerManager = new CompactSerializer($jsonConverter);
        $jws = $serializerManager->unserialize($token);

        $headerCheckerManager = HeaderCheckerManager::create(
            [
                new AlgorithmChecker(['HS256', 'HS384', 'HS512']), // Approved algorithms for ULSv2
            ],
            [
                new JWSTokenSupport(), // We do JWS Tokens
            ]
        );

        $claimCheckerManager = ClaimCheckerManager::create(
            [
                new Checker\IssuedAtChecker(),
                new Checker\NotBeforeChecker(),
                new Checker\ExpirationTimeChecker(),
                new Checker\AudienceChecker($this->facility)
            ]
        );

        // Can throw InvalidHeaderException
        $headerCheckerManager->check($jws, 0);

        // Can throw InvalidClaimException
        $claims = $jsonConverter->decode($jws->getPayload());
        $claimCheckerManager->check($claims);

        $return = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

        $this->token = $token;

        return $return;
    }

    public function getInfo()
    {
        return json_decode($this->curlInfo("GET", $this->buildUrl("info", "token=" . $this->token)), true);
    }

    private function curlInfo($method, $url, $postString = "")
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
           CURLOPT_URL            => $url,
           CURLOPT_RETURNTRANSFER => 1,
           CURLOPT_TIMEOUT        => 15
        ]);
        if ($method == "POST") {
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postString
            ]);
        }
        $response = curl_exec($ch);
        if (!$response) {
            $this->log("PHP-ULS/Curl: error occurred: " . curl_error($ch) . ", error number: #" . curl_errno($ch), "error");
            return false;
        }
        return $response;
    }
}
