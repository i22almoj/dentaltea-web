<?php

namespace App\Controller\API;

use App\Controller\API\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\UserSession;
use App\Entity\User;
use App\Entity\Date;
use App\Entity\Sequence;
use App\Repository\UserRepository;
use DateTime;
use DateInterval;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\ApnsConfig;

class APIController extends BaseController
{
    
/* 
	name: index
	description: Returns a confirmation that the API is working
	path: /api
*/    
    public function index(Request $request): JsonResponse
    {
        return new JsonResponse('API de DentalTEA', Response::HTTP_CREATED, $this->headers);
    }

    
/* 
	name: login
	description: Login method
	path: /api/login
*/     
    public function login(Request $request): JsonResponse
    {   
        $data = json_decode($request->getContent(), true);

        if(empty($data['email'])||empty($data['password'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir email y contraseña'], Response::HTTP_BAD_REQUEST, $this->headers);
        }


        $user = $this->userRepository->findOneBy(['email' => $data['email'], 'password' => md5($data['password'])]);
        if(empty($user))    return new JsonResponse(['success' => false, 'msg' => 'Email o contraseña incorrectos', 'session_id' => $request->headers->get('Authorization')], Response::HTTP_UNAUTHORIZED, $this->headers);

      
        $session_id = $this->JWTManager->create($user);

        return new JsonResponse(['success' => true, 'msg' => 'Sesión creada correctamente', 'data' => ['session_id' => $session_id, 'user_id' => $user->getId()] ], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: privacyPolicy
	description: Returns the privacy policy text
	path: /api/privacy-policy
*/     
    public function privacyPolicy(): JsonResponse 
    {
        $content = $this->renderView('/parts/privacy.html.twig', []);
        return new JsonResponse(['success' => true, 'msg' => 'Texto de política de privacidad', 'data' => ['content' => $content ] ], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: cron
	description: Cron task so that it checks for notifications for users and sends them
	path: /cron
*/     
    public function cron(Request $request){
        date_default_timezone_set('Europe/Madrid');

        $dateTime = date('Y-m-d H:i', strtotime ( '+1 hour' , time()));
        
        $count = $this->checkNotifications($request, $dateTime, 'dentro de una hora');
        
        $dateTime = date('Y-m-d H:i', strtotime ( '+1 day' , time()));

        $count += $this->checkNotifications($request, $dateTime, 'mañana a esta hora');

        return new JsonResponse(['success' => true, 'msg' => 'Tarea cron ejecutada', 'datetime' => date('Y-m-d H:i:s') ,'data' => ['found' => $count] ], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: checkNotifications
	description: Checks if there are notifications for users at a specific date and time and sends them
*/         
    private function checkNotifications(Request $request, $dateTime, $when=null){
        $dates = $this->searchNotifications($dateTime);
        if(empty($dates))   return 0;
        
        foreach($dates as $date){
            if($date->getNotificationsMobile()==1)
                $this->notifyByMobile($request, $date, $when);

            if($date->getNotificationsEmail()==1)
                $this->notifyByEmail($request, $date, $when);
        }

        return sizeof($dates);
    }

/* 
	name: searchNotifications
	description: Searches for active notifications for a specific date and time
*/     
    private function searchNotifications($dateTime){
        $dateTimeFilter = new \DateTime($dateTime.'-00');
        $dateTimeFilter2 = new \DateTime($dateTime.'-59');
        
        $query = $this->doctrine->getManager()->createQuery('
            SELECT d, s, sp, p
            FROM App\Entity\Date d
            LEFT JOIN d.sequence s
            LEFT JOIN s.sequencePictograms sp WITH s.id = sp.sequence
            LEFT JOIN sp.pictogram p  WITH p.id = sp.pictogram
            WHERE d.dateTime >= :dateTime AND d.dateTime <= :dateTime2
            AND ( d.notificationsMobile = 1 OR d.notificationsEmail = 1) 
            ORDER BY sp.sortNumber ASC
        ');
        $query->setParameter('dateTime', $dateTimeFilter);
        $query->setParameter('dateTime2', $dateTimeFilter2);

        return $query->getResult();
    }

/* 
	name: notifyByMobile
	description: Sends a notification to the mobile device
*/      
    private function notifyByMobile(Request $request, $date, $when=null){
        if($when==null) $when = 'el '.$date->getDateTime()->format('d/m/Y').' a las '.$date->getDateTime()->format('H:i');

        $title = 'Tienes una cita '.$when;
        $body = $date->getDateTime()->format('d/m/Y').' '.$date->getDateTime()->format('H:i').'. '.$date->getDescription();

        $factory = (new Factory)->withServiceAccount($this->getParameter('kernel.project_dir').'/dentaltea-a37e7-e99ed76884d0.json');
        $messaging = $factory->createMessaging();
		$notId = $date->getId().'-'.time().'_push';
		$topic = 'notification-'.$date->getAuthor()->getId();
        $base_url = $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath();
        $image_url = $base_url.'/images/logo.png';
        
        $notification = ['notID' => $notId, 'topic' => $topic, 'date_id' => $date->getId(), 'title' => $title, 'body' => $body, 'message' => $body, 'image' => $image_url, 
		'vibrate' => 1, 'sound' => 'default'];
        $apnsConfig = ApnsConfig::fromArray(['headers' => ['apns-priority' => '10',],'payload' => ['aps' => ['alert' => $notification,'badge' => 0,'sound' => 'default']]]);

        //Send message for Android
        $message = CloudMessage::withTarget('topic', $topic)->withData($notification);
        $messaging->send($message);

        //Send message for iOS
        $message = CloudMessage::withTarget('topic', $topic)->withApnsConfig($apnsConfig);
        $messaging->send($message);
    }

/* 
	name: notifyByEmail
	description: Sends an email notification
*/        
    private function notifyByEmail(Request $request, $date, $when=null){ 

        if($when==null) $when = 'el '.$date->getDateTime()->format('d/m/Y').' a las '.$date->getDateTime()->format('H:i');

        $content = '<p>Tienes una cita '.$when.'.</p>';
        $content .= '<p><strong>'.$date->getDateTime()->format('d/m/Y').' '.$date->getDateTime()->format('H:i').'</strong></p><br />';
        
        $sequencePictograms = (!empty($date->getSequence()) ) ? $date->getSequence()->getSequencePictograms() : [];

        $base_url = $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath();
      
        if(!empty($sequencePictograms)){
            $content .= '<hr /><br /><h3 style="font-family:Open sans,arial,sans-serif;font-size:18px;font-weight:600;line-height:23px;text-align:center;color:#363a41;">Apoyo visual</h3><br />';
            $content .= '<table style="width:100%;">';
            $i=0;
            foreach($sequencePictograms as $item){ 
                $i++;
                if($i%2==1)   $content .= '<tr>';
                $content .= '<td style="padding: 0 15px 30px; font-size: 12px; line-height: 16px;"><img src="'.$base_url.'/'.$item->getPictogram()->getImage().'" width="200" style="max-width:100%; height: auto; margin-bottom: 10px;"/><br />'.$item->getDescription().'</td>';
                if($i%2==0) $content .= '</tr>';
            }
            if($i%2==0) $content .= '</tr>';
            
            $content .= '</table>';
        }
        $email = (new TemplatedEmail())
        ->to($date->getAuthor()->getEmail())
        ->htmlTemplate('emails/base.html.twig')
        ->subject('Recordatorio de cita')
        ->context([
            'subject' => 'Recordatorio de cita',
            'title' => 'Recordatorio de cita',
            'content' => $content,
            'base_url' => $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath()
        ]);
     

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            echo $e->getDebug();
            // error message or try to resend the message
    
        } 
        
    }

}