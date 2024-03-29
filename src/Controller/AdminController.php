<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 20/04/2017
 * Time: 12:00
 */

namespace Itb\Controller;

use Itb\Model\RecipesRepository;
use Itb\model\Users;
use Itb\model\UsersRepository;
use Itb\WebApplication;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class AdminController
{
    /*
     * Class AdminController
     *
     * simple authentication using Silex session object
     * $app['session']->set('isAuthenticated', false);
     */
    private $app;
    public function __construct(WebApplication $app)
    {
        $this->app = $app;
    }

    /**
     * action for route: user login /index
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request, Application $app)
    {
        // test if 'username' stored in session ...
        $username = $this->getAuthenticatedUserName();
        // check we are authenticated --------
        $isAuthenticated = (null != $username);
        if(!$isAuthenticated){
            // not authenticated, so redirect to LOGIN page
            return $app->redirect('/login');
        }
        $recipesRepository = new RecipesRepository();
        $recipes = $recipesRepository->getAllRecipes();
        // store username into args array
        $argsArray = ['recipes' => $recipes];
        // render (draw) template
        $templateName = 'admin/index';
        return $app['twig']->render($templateName . '.html.twig', $argsArray);
    }

    /**
     * route for users recipes
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function codesAction(Request $request, Application $app)
    {
        $username = $this->getAuthenticatedUserName();
        // check we are authenticated
        $isAuthenticated = (null != $username);
        if(!$isAuthenticated){
            // not authenticated, so redirect to LOGIN page
            return $app->redirect('/login');
        }
        // store username into args array
        $argsArray = [ ];
        // render (draw) template
        $templateName = 'admin/codes';
        return $app['twig']->render($templateName . '.html.twig', $argsArray);
    }

    /**
     * retrieve users name
     * @return $user username or null
     */
    public function getAuthenticatedUserName()
    {
        // IF object (array) 'user' found with non-null value in 'session'
        $user = $this->app['session']->get('user');

        // if no such object in session, return NULL
        if(null == $user){
            return null;
        }
        // IF no value found in $user with key 'username' then return NULL
        if (!isset($user['username'])){
            return null;
        }
        // if delete User button is pressed
        if (isset($_GET['deleteUser'])){
            $request = $this->app['request_stack']->getCurrentRequest();
            $delete = $request->get('deleteUser');
            $users = new Users();
            $id = $users->getId();
            $con = mysqli_connect('localhost', 'root', '', 'user_roles');
            $result = mysqli_query($con, 'DELETE FROM users WHERE id="' . $delete . '"');
            if(isset($_GET['bttDelete'])) {
                if ($result == $id) {
                    $usersRepository = new UsersRepository();
                    $users = $usersRepository->removeUsers($id);
                    unset($this->$users[$id]);
                }
            }
        }
        // if we get here, we can return the value whose key is 'username'
        return $user['username'];
    }
}