<?php
/**
 * \DragonBe\Vies
 *
 * Component using the European Commission (EC) VAT Information Exchange System (VIES) to verify and validate VAT
 * registration numbers in the EU, using PHP and Composer.
 *
 * @author Michelangelo van Dam <dragonbe+github@gmail.com>
 * @license MIT
 *
 */
namespace DragonBe\Vies;
/**
 * Class Vies
 * 
 * This class provides a soap client for usage of the VIES web service
 * provided by the European Commission to validate VAT numbers of companies
 * registered within the European Union
 * 
 * @category DragonBe
 * @package \DragonBe\Vies
 * @link http://ec.europa.eu/taxation_customs/vies/faqvies.do#item16
 */
class Vies
{
    const VIES_PROTO = 'http';
    const VIES_DOMAIN = 'ec.europa.eu';
    const VIES_WSDL = '/taxation_customs/vies/checkVatService.wsdl';

    /**
     * @var \SoapClient
     */
    protected $soapClient;

    /**
     * @var string The WSDL for VIES service
     */
    protected $wsdl;

    /**
     * @var array Options for the SOAP client
     */
    protected $options;

    /**
     * @var HeartBeat A heartbeat checker to verify if the VIES service is available
     */
    protected $heartBeat;

    /**
     * Retrieves the SOAP client that will be used to communicate with the VIES
     * SOAP service.
     *
     * @return \SoapClient
     */
    public function getSoapClient()
    {
        if (null === $this->soapClient) {
            $this->soapClient = new \SoapClient(
                $this->getWsdl(),
                $this->getOptions()
            );
        }
        return $this->soapClient;
    }

    /**
     * Sets the PHP SOAP Client and allows you to override the use of the native
     * PHP SoapClient for testing purposes or for better integration in your own
     * application.
     *
     * @param \SoapClient $soapClient
     * @return Vies
     */
    public function setSoapClient($soapClient)
    {
        $this->soapClient = $soapClient;
        return $this;
    }

    /**
     * Retrieves the location of the WSDL for the VIES SOAP service
     *
     * @return string
     */
    public function getWsdl()
    {
        if (null === $this->wsdl) {
            $this->wsdl = sprintf('%s://%s%s',
                self::VIES_PROTO,
                self::VIES_DOMAIN,
                self::VIES_WSDL
            );
        }
        return $this->wsdl;
    }

    /**
     * Sets the location of the WSDL for the VIES SOAP Service
     *
     * @param string $wsdl
     * @return Vies
     * @example http://ec.europa.eu//taxation_customs/vies/checkVatService.wsdl
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;
        return $this;
    }

    /**
     * Retrieves the options for the PHP SOAP service
     *
     * @return array
     */
    public function getOptions()
    {
        if (null === $this->options) {
            $this->options = [];
        }
        return $this->options;
    }

    /**
     * Set options for the native PHP Soap Client
     *
     * @param array $options
     * @return Vies
     * @link http://php.net/manual/en/soapclient.soapclient.php
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Retrieves the heartbeat class that offers the option to check if the VIES
     * service is up-and-running.
     *
     * @return HeartBeat
     */
    public function getHeartBeat()
    {
        if (null === $this->heartBeat) {
            $this->setHeartBeat(
                new HeartBeat(
                    'tcp://' . self::VIES_DOMAIN,
                    80
                )
            );
        }
        return $this->heartBeat;
    }

    /**
     * Sets the heartbeat functionality to verify if the VIES service is alive or not,
     * especially since this service tends to have a bad reputation of its availability.
     *
     * @param HeartBeat $heartBeat
     */
    public function setHeartBeat(HeartBeat $heartBeat)
    {
        $this->heartBeat = $heartBeat;
    }

    /**
     * Validates a given country code and VAT number and returns a
     * \DragonBe\Vies\CheckVatResponse object
     * 
     * @param string $countryCode The two-character country code of a European
     * member country
     * @param string $vatNumber The VAT number (without the country 
     * identification) of a registered company
     * @return \DragonBe\Vies\CheckVatResponse
     */
    public function validateVat($countryCode,$vatNumber)
    {
        $vatNumber = self::filterVat($vatNumber);
        $response = $this->getSoapClient()->__soapCall(
            'checkVat',
            array (
                array (
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber
                )
            )
        );
        return new CheckVatResponse($response);
    }
    /**
     * Filters a VAT number and normalizes it to an alfanumeric string
     * 
     * @param string $vatNumber
     * @return string
     * @static
     */
    public static function filterVat($vatNumber)
    {
        return str_replace(array (' ', '.', '-'), '', $vatNumber);
    }

    /**
     * Checks if the VIES service is up and available for usage
     *
     * @return bool
     */
    public function isAlive()
    {
        return false;
    }
}
