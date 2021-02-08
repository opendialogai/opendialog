<?php

namespace Tests\Feature;

use App\ImportExportHelpers\DynamicAttributeImportExportHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use OpenDialogAi\AttributeEngine\DynamicAttribute;
use Tests\TestCase;

/**
 * Class ImportExportIntentsTest
 *
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportDynamicAttributesTest extends TestCase
{

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    public function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::fake('specification');
    }

    public function testImportInvalidJSON()
    {
        $notJSON = '
            { "test_dynamic_attribute_a" => "attribute.core.string",
            "test_dynamic_attribute_b" => "attribute.core.int",
        ';

        $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('good-custom-attributes'), $notJSON);

        Artisan::call('custom-attributes:import', [
                '--yes' => true, 'name' => 'good-custom-attributes'
        ]);


        $this->assertDatabaseMissing('dynamic_attributes',
            ['attribute_id' => "test_dynamic_attribute_a", 'attribute_type' => "attribute.core.string"]);
        $this->assertDatabaseMissing('dynamic_attributes',
            ['attribute_id' => "test_dynamic_attribute_b", 'attribute_type' => "attribute.core.int"]);

    }

    public function testImportInvalidAttributeIds()
    {
        foreach (DynamicAttributesTest::INVALID_IDS as $id) {
            $data = [$id => 'attribute.core.string'];
            $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('invalid-ids-attributes'),
                json_encode($data));

            Artisan::call('custom-attributes:import', [
                    '--yes' => true, 'name' => 'invalid-ids-attributes'
                ]);

            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => $id, 'attribute_type' => "attribute.core.string"]);

        }

    }

    public function testImportInvalidAttributeTypes()
    {

        foreach (DynamicAttributesTest::INVALID_TYPES as $type) {
            $data = ["dynamic_attribute_test" => $type];
            $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('invalid-types-attributes'),
                json_encode($data));

            Artisan::call('custom-attributes:import', [
                    '--yes' => true, 'name' => 'invalid-types-attributes'
                ]);

            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => "dynamic_attribute_test", 'attribute_type' => $type]);
        }

    }

    public function testSuccessfulImport()
    {

        $goodData = [
            'test_dynamic_attribute_a' => 'attribute.core.string', 'test_dynamic_attribute_b' => 'attribute.core.int',
            'test_dynamic_attribute_c' => 'attribute.core.string'
        ];

        $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('good-custom-attributes'),
            json_encode($goodData));

        Artisan::call('custom-attributes:import', [
                '--yes' => true, 'name' => 'good-custom-attributes'
            ]);

        foreach ($goodData as $attribute_id => $attribute_type) {
            $this->assertDatabaseHas('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }

    }

    public function testImportDeleteExisting()
    {
        $existing = [
            'test_dynamic_attribute_a' => 'attribute.core.string',
            'test_dynamic_attribute_b' => 'attribute.core.string',
            'test_dynamic_attribute_c' => 'attribute.core.string'
        ];
        foreach($existing as $attribute_id => $attribute_type) {
            DynamicAttribute::create(['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }

        $replacement = [
            'test_dynamic_attribute_a' => 'attribute.core.int',
            'test_dynamic_attribute_b' => 'attribute.core.int',
        ];
        $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('custom-attributes'),
            json_encode($replacement));

        Artisan::call('custom-attributes:import', [
            'name' => 'custom-attributes',
            '--delete-existing' => true,
            '--yes' => true
        ]);

        $this->assertEquals(2, DynamicAttribute::all()->count());
        foreach ($replacement as $attribute_id => $attribute_type) {
            $this->assertDatabaseHas('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }
    }

    public function testImportDuplicateIds()
    {
        $existing = [
            'test_dynamic_attribute_a' => 'attribute.core.string',
            'test_dynamic_attribute_b' => 'attribute.core.string',
            'test_dynamic_attribute_c' => 'attribute.core.string'
        ];
        foreach($existing as $attribute_id => $attribute_type) {
            DynamicAttribute::create(['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }

        $replacement = [
            'test_dynamic_attribute_a' => 'attribute.core.int',
            'test_dynamic_attribute_b' => 'attribute.core.int',
        ];
        $this->disk->put(DynamicAttributeImportExportHelper::getFilePath('custom-attributes'),
            json_encode($replacement));


        $expectedOutput = "Importing custom attributes...\n"."Some ids are already in use.\n"."IDs:\n"
            ."* test_dynamic_attribute_a\n"."* test_dynamic_attribute_b\nFailed to create collection. Restoring database...\n";

        Artisan::call('custom-attributes:import', [
            'name' => 'custom-attributes',
            '--yes' => true
        ]);

        $this->assertEquals($expectedOutput, Artisan::output());

        foreach ($existing as $attribute_id => $attribute_type) {
            $this->assertDatabaseHas('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }
        foreach ($replacement as $attribute_id => $attribute_type) {
            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }
    }

    public function testExportSuccess()
    {
        for ($i = 0; $i < 3; $i++) {
            factory(DynamicAttribute::class)->create();
        }

        Artisan::call('custom-attributes:export', [
                '--yes' => true, 'name' => 'custom-attributes'
            ]);

        $this->disk->assertExists(DynamicAttributeImportExportHelper::getFilePath("custom-attributes"));
        $fileData = $this->disk->get(DynamicAttributeImportExportHelper::getFilePath("custom-attributes"));

        $array = json_decode($fileData, JSON_OBJECT_AS_ARRAY);
        $this->assertIsArray($array);
        $this->assertEquals(count($array), 3);

        foreach ($array as $id => $type) {
            $dynamicAttribute = DynamicAttribute::firstWhere('attribute_id', $id);
            $this->assertNotNull($dynamicAttribute);
            $this->assertEquals($dynamicAttribute->attribute_id, $id);
            $this->assertEquals($dynamicAttribute->attribute_type, $type);
        }
    }

    public function testExportOverwrite()
    {

        $name = 'custom-attributes';

        $dummyData = "Dummy file data";
        $this->disk->put(DynamicAttributeImportExportHelper::getFilePath($name), $dummyData);

        Artisan::call('custom-attributes:export', [
                '--yes' => true, '--overwrite' => false, 'name' => $name
            ]);

        /* Check file is not overwritten */
        $this->disk->assertExists(DynamicAttributeImportExportHelper::getFilePath($name));
        $this->assertEquals($dummyData, $this->disk->get(DynamicAttributeImportExportHelper::getFilePath($name)));


        for ($i = 0; $i < 3; $i++) {
            factory(DynamicAttribute::class)->create();
        }
        Artisan::call('custom-attributes:export', [
                '--yes' => true, '--overwrite' => true, 'name' => $name
            ]);

        $fileData = $this->disk->get(DynamicAttributeImportExportHelper::getFilePath($name));
        $array = json_decode($fileData, JSON_OBJECT_AS_ARRAY);
        $this->assertIsArray($array);
        $this->assertEquals(count($array), 3);

        foreach ($array as $id => $type) {
            $dynamicAttribute = DynamicAttribute::firstWhere('attribute_id', $id);
            $this->assertNotNull($dynamicAttribute);
            $this->assertEquals($dynamicAttribute->attribute_id, $id);
            $this->assertEquals($dynamicAttribute->attribute_type, $type);
        }
    }
}
