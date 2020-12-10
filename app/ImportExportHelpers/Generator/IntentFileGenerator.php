<?php


namespace App\ImportExportHelpers\Generator;

use App\ImportExportHelpers\IntentImportExportHelper;

class IntentFileGenerator implements \Stringable
{
    /**
     * @var string
     */
    private $name;

    /**
     * IntentFileGenerator constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $fileData
     * @return static
     * @throws InvalidFileFormatException
     */
    public static function fromString(string $fileData): self
    {
        $xml = new \SimpleXMLElement($fileData);

        if ($xml->getName() !== IntentImportExportHelper::INTENT_FILE_ROOT_ELEMENT) {
            throw new InvalidFileFormatException("Root element not " . IntentImportExportHelper::INTENT_FILE_ROOT_ELEMENT);
        }

        $intentName = (string) $xml->name;

        if (empty($intentName)) {
            throw new InvalidFileFormatException("Missing intent element");
        }

        return new self($intentName);
    }

    public function __toString()
    {
        return <<<EOT
<intent>
    <name>$this->name</name>
</intent>
EOT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return IntentFileGenerator
     */
    public function setName(string $name): IntentFileGenerator
    {
        $this->name = $name;
        return $this;
    }
}
