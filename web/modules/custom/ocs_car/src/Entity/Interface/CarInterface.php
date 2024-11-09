<?php

declare(strict_types=1);

namespace Drupal\ocs_car\Entity\Interface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a car entity type.
 */
interface CarInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
