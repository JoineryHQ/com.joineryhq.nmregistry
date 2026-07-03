<?php

namespace Civi\Nmregistry;

use Civi\Core\Service\AutoSubscriber;
use Civi\Token\Event\TokenRegisterEvent;
use Civi\Token\Event\TokenValueEvent;
class Token extends AutoSubscriber {

  public static function getSubscribedEvents() {
    return [
      'civi.token.list' => 'list',
      'civi.token.eval' => 'eval',
    ];
  }

  public function list(TokenRegisterEvent $e) {
    $e->entity('activity')
      ->register('registry_activity_source_name', ts('Source contact name'))
      ->register('registry_activity_source_email_primary', ts('Source contact primary email'));
  }

  public function eval(TokenValueEvent $e): void {
    foreach ($e->getRows() as $row) {
      $rowContext = $row->tokenProcessor->rowContexts[$row->tokenRow];
      if($activityId = $rowContext['activityId']) {
        $activity = \Civi\Api4\Activity::get()
          ->setCheckPermissions(FALSE)
          ->addSelect('id')
          ->addWhere('id', '=', $activityId)
          ->addChain('source', \Civi\Api4\ActivityContact::get()
            ->setCheckPermissions(FALSE)
            ->addSelect('record_type_id', 'contact_id')
            ->addWhere('activity_id', '=', '$id')
            ->addWhere('record_type_id', '=', '2')
            ->setLimit(1)
            ->addChain('contact', \Civi\Api4\Contact::get()
              ->setCheckPermissions(FALSE)
              ->addSelect('email_primary.email', 'display_name')
              ->addWhere('id', '=', '$contact_id')
            )
          )
          ->execute()
          ->first();
        $row->tokens('activity', 'registry_activity_source_name', $activity['source'][0]['contact'][0]['display_name']);
        $row->tokens('activity', 'registry_activity_source_email_primary', $activity['source'][0]['contact'][0]['email_primary.email']);
      }
    }    
  }

}