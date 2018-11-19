<?php
/**
 * Created by PhpStorm.
 * User: corentinboutillier
 * Date: 19/11/2018
 * Time: 13:24
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailResetType;
use App\Form\ResetType;
use App\Form\UserType;
use App\Security\SecurityMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{



    /**************************/
    /*     LOGIN / LOGOUT     */
    /**************************/

    /**
     * @Route("/login", name="login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     * @IsGranted("ROLE_USER")
     */
    public function logout() {}




    /************************/
    /*       REGISTER       */
    /************************/

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param SecurityMailer $securityMailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, SecurityMailer $securityMailer)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $token = $this->uuidGeneration();
            $user->setPassword($password)
                ->setEmailConfirmation($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $securityMailer->sendEmailConfirmation($user->getEmail(),
                $this->generateUrl('email_confirmation',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL));

            // TODO: Rediriger vers notification d'envoi de mail
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/email_confirmation", name="email_confirmation")
     *
     * @param Request $request
     * @return Response
     */
    public function emailConfirmation(Request $request) {

        $entityManager = $this->getDoctrine()->getManager();
        $token = $request->query->get('token');

        $user = $entityManager->getRepository(User::class)->findOneByEmailConfirmation($token);

        if (!$user || is_null($token)) {
            throw $this->createNotFoundException('Page does not exist');
        } else {
            $user->setIsActive(true)
                ->setEmailConfirmation(null);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('security/email-confirmation.html.twig');
    }




    /************************/
    /*       PASSWORD       */
    /************************/

    /**
     * @Route("/forgot_password", name="forgot_password")
     *
     * @param Request $request
     * @param SecurityMailer $securityMailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function resetPassword(Request $request, SecurityMailer $securityMailer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(EmailResetType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $entityManager->getRepository(User::class)->findOneByEmail($form->getData()['email']);
            if ($user !== null) {
                $token = $this->uuidGeneration();
                $user->setResetPassword($token);
                $entityManager->persist($user);
                $entityManager->flush();

                $securityMailer->sendResetPasswordMail($user->getEmail(), $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL));

                return $this->redirectToRoute('reset_password_email_send');
            }
        }

        return $this->render('password/reset-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset_password/email/send", name="reset_password_email_send")
     *
     * @return Response
     */
    public function resetPasswordEmailSend() {
        return $this->render('password/reset-password-email-send.html.twig');
    }

    /**
     * @Route("/reset_password", name="reset_password")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetPasswordToken(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $token = $request->query->get('token');
        if ($token !== null) {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByResetPassword($token);
            if ($user !== null) {
                $form = $this->createForm(ResetType::class);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $plainPassword = $form->getData()["plainPassword"];
                    $encoded = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($encoded)
                        ->setResetPassword(null);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    return $this->redirectToRoute('login');
                }

                return $this->render('password/reset-password-token.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }
        throw $this->createNotFoundException('Page does not exist');
    }

    /**
     * @Route("/change_password", name="change_password")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder) {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

         $form = $this->createForm(ResetType::class);

         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {

             $plainPassword = $form->getData()["plainPassword"];
             $encoded = $encoder->encodePassword($user, $plainPassword);
             $user->setPassword($encoded);

             $entityManager->persist($user);
             $entityManager->flush();

             return $this->redirectToRoute('login');
         }

         return $this->render('password/change-password.html.twig', [
             'form' => $form->createView(),
         ]);
    }




    /***********************/
    /*       PRIVATE       */
    /***********************/

    /**
     * @return string
     */
    private function uuidGeneration() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
