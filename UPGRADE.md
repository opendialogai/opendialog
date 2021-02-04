# Upgrade guide

## 0.7.x to 1.0.x

### Attribute Engine

OpenDialog core now has a separate Attribute Engine for managing attributes. Previously the Context Engine managed attributes as well as contexts, but a clearer separation of concerns has now been made. This update requires the follow changes to your application:

- The `AttributeResolver` facade should now be imported from `OpenDialogAi\AttributeEngine\Facades` rather than `OpenDialogAi\ContextEngine\Facades`.
- All core attribute types (`StringAttribute`, `IntAttribute`, etc) should now be imported from `OpenDialogAi\AttributeEngine\Attributes` rather than `OpenDialogAi\Core\Attribute`.
- The `context_engine.php` config file should no longer define `custom_attributes`. Run `php artisan vendor:publish --tag=opendialog-config` to publish the new `attribute_engine.php` and move your `custom_attributes` configuration there.
- If you use any custom attribute types, you'll now need to register these under `custom_attribute_types` in the new `attribute_engine.php`.
