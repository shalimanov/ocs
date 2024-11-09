<?php

declare(strict_types=1);

namespace Drupal\ocs_car\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\ocs_car\Entity\Interface\CarInterface;

/**
 * Defines the car entity class.
 *
 * @ContentEntityType(
 *   id = "ocs_car",
 *   label = @Translation("Car"),
 *   label_collection = @Translation("Cars"),
 *   label_singular = @Translation("car"),
 *   label_plural = @Translation("cars"),
 *   label_count = @PluralTranslation(
 *     singular = "@count cars",
 *     plural = "@count cars",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\ocs_car\CarListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\ocs_car\CarAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ocs_car\Form\CarForm",
 *       "edit" = "Drupal\ocs_car\Form\CarForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "ocs_car",
 *   data_table = "ocs_car_field_data",
 *   revision_table = "ocs_car_revision",
 *   revision_data_table = "ocs_car_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer ocs_car",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/ocs-car",
 *     "add-form" = "/car/add",
 *     "canonical" = "/car/{ocs_car}",
 *     "edit-form" = "/car/{ocs_car}/edit",
 *     "delete-form" = "/car/{ocs_car}/delete",
 *     "delete-multiple-form" = "/admin/content/ocs-car/delete-multiple",
 *     "revision" = "/car/{ocs_car}/revision/{ocs_car_revision}/view",
 *     "revision-delete-form" = "/car/{ocs_car}/revision/{ocs_car_revision}/delete",
 *     "revision-revert-form" = "/car/{ocs_car}/revision/{ocs_car_revision}/revert",
 *     "version-history" = "/car/{ocs_car}/revisions",
 *   },
 *   field_ui_base_route = "entity.ocs_car.settings",
 * )
 */
final class Car extends RevisionableContentEntityBase implements CarInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(
    EntityStorageInterface $storage,
  ): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(
    EntityTypeInterface $entity_type,
  ): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the car was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the car was last edited.'));

    $fields['model'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Model'))
      ->setDescription(t('The model of the car.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', ['target_bundles' => ['make_model']])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Year'))
      ->setDescription(t('The manufacturing year of the car.'))
      ->setSetting('min', 1886)
      ->setSetting('max', date('Y') + 1)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Price'))
      ->setDescription(t('The price of the car, including currency.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_formatted_amount',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['kilometrage'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Kilometrage'))
      ->setDescription(t('The car\'s distance traveled in kilometers.'))
      ->setSetting('min', 0)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['fuel_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Fuel Type'))
      ->setDescription(t('The type of fuel used by the car.'))
      ->setSettings([
        'allowed_values' => [
          'petrol' => 'Petrol',
          'diesel' => 'Diesel',
          'electric' => 'Electric',
          'hybrid' => 'Hybrid',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['transmission_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Transmission Type'))
      ->setDescription(t('The transmission type of the car.'))
      ->setSettings([
        'allowed_values' => [
          'manual' => 'Manual',
          'automatic' => 'Automatic',
          'semi_automatic' => 'Semi-Automatic',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['body_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Body Type'))
      ->setDescription(t('The body type of the car.'))
      ->setSettings([
        'allowed_values' => [
          'sedan' => 'Sedan',
          'suv' => 'SUV',
          'hatchback' => 'Hatchback',
          'convertible' => 'Convertible',
          'coupe' => 'Coupe',
          'wagon' => 'Wagon',
          'van' => 'Van',
          'truck' => 'Truck',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['photos'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Photos'))
      ->setDescription(t('Upload images of the car.'))
      ->setSetting('target_type', 'file')
      ->setSetting('handler', 'default')
      ->setSetting('file_extensions', 'png jpg jpeg')
      ->setSetting('uri_scheme', 'public')
      ->setSetting('default_image', [])
      ->setSetting('max_filesize', '5 MB')
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'settings' => [
          'file_directory' => 'car_photos',
          'alt_field' => TRUE,
          'title_field' => TRUE,
          'file_extensions' => 'png jpg jpeg',
        ],
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 10,
        'settings' => [
          'image_style' => 'thumbnail',
          'image_link' => 'file',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $fields['color'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Color'))
      ->setDescription(t('The color of the car.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', ['target_bundles' => ['colors']])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['location'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Location'))
      ->setCardinality(-1)
      ->setDescription(t('The city or region of the car\'s location.'))
      ->setSettings([
        'field_override' => TRUE,
        'available_countries' => ['UA'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'address',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'address_plain',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
