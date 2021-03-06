<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginType;
use AppBundle\Form\RegistrationType;
use AppBundle\Transfer\LoginTransfer;
use AppBundle\Transfer\RegistrationTransfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    const HOUR_IN_SECONDS = 3600;
    const DAY_IN_HOURS = 24;
    const YEAR_IN_DAYS = 365;

    /**
     * @param Request $request
     *
     * @return Response|RedirectResponse
     *
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        if ($this->checkIfLoggedIn()) {
            return $this->redirectToRoute('home', [
                'request' => $request,
            ]);
        }

        $registrationTransferObject = new RegistrationTransfer();
        $registrationForm = $this->initForm($request, new RegistrationType(), $registrationTransferObject);

        $loginTransferObject = new LoginTransfer();
        $loginForm = $this->initForm($request, new LoginType(), $loginTransferObject);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $authSecret = $this->get('app.redis.redis_registration')->register(
                $registrationTransferObject->getUsername(),
                $registrationTransferObject->getPassword()
            );

            return $this->redirectToRoute('home', [
                'request' => $request,
                'cookie' => $this->generateCookie($authSecret)
            ]);
        }

        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $authSecret = $this->get('app.redis.redis_login')->login(
                $loginTransferObject->getUsername(),
                $loginTransferObject->getPassword()
            );

            return $this->redirectToRoute('home', [
                'request' => $request,
                'cookie' => $this->generateCookie($authSecret)
            ]);
        }

        return $this->render(':default:index.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'loginForm'        => $loginForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('app.redis.redis_logout')->logout();

        return $this->redirectToRoute('index', [
            'request' => $request,
        ]);
    }

    /**
     * @param Request $request
     * @param AbstractType $formType
     * @param $transferObject
     *
     * @return Form
     */
    private function initForm(Request $request, AbstractType $formType, $transferObject)
    {
        $form = $this->createForm($formType, $transferObject);
        $form->handleRequest($request);
        return $form;
    }

    /**
     * @param $authSecret
     *
     * @return Cookie
     */
    private function generateCookie($authSecret)
    {
        $cookie = new Cookie(
            'auth',
            $authSecret,
            new \DateTime(self::HOUR_IN_SECONDS * self::DAY_IN_HOURS * self::YEAR_IN_DAYS)
        );

        return $cookie;
    }

    /**
     * @return boolean
     */
    private function checkIfLoggedIn()
    {
        $userId = $this->get('session')->get('userId', null);
        $username = $this->get('session')->get('username', null);
        if ($userId !== null && $username !== null) {
            return true;
        }

        return false;
    }
}
