<?php
namespace Zidisha\Lender;

use Propel\Runtime\Propel;
use Zidisha\Analytics\MixpanelService;
use Zidisha\Balance\Map\TransactionTableMap;
use Zidisha\Balance\TransactionService;
use Zidisha\Mail\LenderMailer;

class LenderService
{

    private $lenderMailer;
    private $mixpanelService;
    private $transactionService;

    public function __construct(
        LenderMailer $lenderMailer,
        MixpanelService $mixpanelService,
        TransactionService $transactionService
    ) {
        $this->lenderMailer = $lenderMailer;
        $this->mixpanelService = $mixpanelService;
        $this->transactionService = $transactionService;
    }

    public function editProfile(Lender $lender, $data)
    {
        $lender->setFirstName($data['firstName']);
        $lender->setLastName($data['lastName']);
        $lender->getUser()->setEmail($data['email']);
        $lender->getUser()->setUsername($data['username']);
        $lender->getProfile()->setAboutMe($data['aboutMe']);

        if (!empty($data['password'])) {
            $lender->getUser()->setPassword($data['password']);

        }

        $lender->save();
    }

    public function uploadPicture(Lender $lender, $image)
    {
        $user = $lender->getUser();

        if ($image) {
            $upload = Upload::createFromFile($image);
            $upload->setUser($user);
            $user->setProfilePicture($upload);
            $user->save();
        }
    }

    public function lenderInviteViaEmail($lender, $email, $subject, $custom_message)
    {
        $lender_invite = new Invite();
        $lender_invite->setLender($lender);
        $lender_invite->setEmail($email);
        $lender_invite->isInvited(true);
        $success = $lender_invite->save();

        if ($success) {
            $this->lenderMailer->sendLenderInvite($lender, $lender_invite, $subject, $custom_message);
        }

        return $lender_invite;
    }

    public function addLenderInviteVisit(Lender $lender, $shareType, Invite $invite = null)
    {
        $inviteVisit = new InviteVisit();
        $inviteVisit->setLender($lender);
        $inviteVisit->setInvite($invite);
        $inviteVisit->setShareType($shareType);
        $inviteVisit->setIpAddress(\Request::getClientIp());
        $inviteVisit->setHttpReferer(array_get($_SERVER, 'HTTP_REFERER', ""));
        $inviteVisit->save();

        $this->mixpanelService->trackInvitePage($lender, $inviteVisit, $shareType);

        return $inviteVisit;
    }


    function processLenderInvite(Lender $invitee, InviteVisit $lenderInviteVisit)
    {
        $con = Propel::getWriteConnection(TransactionTableMap::DATABASE_NAME);
        for ($retry = 0; $retry < 3; $retry++) {
            $con->beginTransaction();
            try {
                $invite = $lenderInviteVisit->getInvite();
                if ($invite) {
                    $res1 = $invite->setInvitee($invitee)->save();
                } else {
                    $invite = new Invite();
                    $invite->setLender($lenderInviteVisit->getLender());
                    $invite->setEmail($invitee->getUser()->getEmail());
                    $invite->setInvitee($invitee);
                    $invite->setInvited(false);
                    $res1 = $invitee->save($con);
                }
                if (!$res1) {
                    throw new \Exception();
                }
                $this->transactionService->addLenderInviteTransaction($con, $invite);
            } catch (\Exception $e) {
                $con->rollback();
            }
            $con->commit();

            //TODO , invite_notify(see below commented if statement)
            //   if ($lender['invite_notify']) {
            $this->lenderMailer->sendLenderInviteCredit($invite);
            // }
            $this->mixpanelService->trackInviteAccept($invite);
            return $invite;
        }

        return false;
    }
}
