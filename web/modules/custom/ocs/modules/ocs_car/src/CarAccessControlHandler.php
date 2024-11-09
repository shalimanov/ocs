<?php

declare(strict_types=1);

namespace Drupal\ocs_car;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the car entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class CarAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(
    EntityInterface $entity,
    $operation,
    AccountInterface $account,
  ): AccessResult {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return match ($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view ocs_car'),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit ocs_car'),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete ocs_car'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete ocs_car revision'),
      'view all revisions', 'view revision' => AccessResult::allowedIfHasPermissions($account, [
        'view ocs_car revision',
        'view ocs_car',
      ]),
      'revert' => AccessResult::allowedIfHasPermissions($account, [
        'revert ocs_car revision',
        'edit ocs_car',
      ]),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(
    AccountInterface $account,
    array $context,
    $entity_bundle = NULL,
  ): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, [
      'create ocs_car',
      'administer ocs_car',
    ], 'OR');
  }

}
