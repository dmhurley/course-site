<?php
namespace Bio\DataBundle\Type;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\Yaml\Yaml;

/**
 * My custom datatype.
 */
class PrivateTextType extends StringType
{
    const MYTYPE = 'privatestring'; // modify to match your type name
    private $salt;

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return trim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $this->getSalt(),
                base64_decode($value),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    )
                    ,MCRYPT_RAND
                )
            )
        );
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return trim(
            base64_encode(
                mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_256,
                    $this->getSalt(),
                    $value,
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_256,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_RAND
                    )
                )
            )
        );
    }

    public function getName()
    {
        return self::MYTYPE; // modify to match your constant name
    }

    public function getSalt() {
        if (isset($this->salt)){
            return $this->salt;
        } else {
            $parameters = Yaml::parse(file_get_contents(__DIR__.'/../../../../app/config/parameters.yml'));
            $this->salt = $parameters['parameters']['secret'];
        }
        return $this->salt;
    }
}
