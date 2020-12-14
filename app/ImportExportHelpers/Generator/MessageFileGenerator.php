<?php


namespace App\ImportExportHelpers\Generator;

use App\ImportExportHelpers\MessageImportExportHelper;
use DOMDocument;

class MessageFileGenerator implements \Stringable
{
    /**
     * @var string
     */
    private $intent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ?string
     */
    private $conditions;

    /**
     * @var string
     */
    private $markup;

    /**
     * @param string $intent
     * @param string $name
     * @param string $markup
     */
    public function __construct(string $intent, string $name, string $markup)
    {
        $this->intent = $intent;
        $this->name = $name;
        $this->markup = $markup;
    }

    /**
     * @param string $fileData
     * @return static
     * @throws InvalidFileFormatException
     */
    public static function fromString(string $fileData): self
    {
        $xml = new \SimpleXMLElement($fileData);

        if ($xml->getName() !== MessageImportExportHelper::MESSAGE_FILE_ROOT_ELEMENT) {
            throw new InvalidFileFormatException("Root element not " . MessageImportExportHelper::MESSAGE_FILE_ROOT_ELEMENT);
        }

        $messageName = (string) $xml->name;

        if (empty($messageName)) {
            throw new InvalidFileFormatException("Missing name element");
        }

        $intentName = (string) $xml->intent;

        if (empty($intentName)) {
            throw new InvalidFileFormatException("Missing intent element");
        }

        $markup = $xml->markup->message->asXML();

        if (empty($markup)) {
            throw new InvalidFileFormatException("Missing markup element");
        }

        $fileGenerator = new self($intentName, $messageName, $markup);
        $fileGenerator->setConditions($xml->conditions);

        return $fileGenerator;
    }

    public function __toString()
    {
        $xml = new \SimpleXMLElement("<parent></parent>");
        $xml->addChild('message-template');
        $xml->{'message-template'}->addChild('intent', $this->intent);
        $xml->{'message-template'}->addChild('name', $this->name);

        if (isset($this->conditions) && !is_null($this->conditions)) {
            $xml->{'message-template'}->addChild('conditions', $this->conditions);
        }

        $xml->{'message-template'}->addChild('markup');

        $data = $xml->asXML();

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($data);

        $markup = $dom->saveXML($dom->getElementsByTagName('message-template')[0]);
        $markup = str_replace('<markup/>', sprintf('<markup>%s</markup>', $this->markup), $markup);

        return $markup;
    }

    /**
     * @return string
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     * @return self
     */
    public function setIntent(string $intent): self
    {
        $this->intent = $intent;
        return $this;
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
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    /**
     * @param string|null $conditions
     * @return self
     */
    public function setConditions(?string $conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return string
     */
    public function getMarkup(): string
    {
        return $this->markup;
    }

    /**
     * @param string $markup
     * @return self
     */
    public function setMarkup(string $markup): self
    {
        $this->markup = $markup;
        return $this;
    }
}
