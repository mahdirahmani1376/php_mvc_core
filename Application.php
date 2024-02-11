<?php

namespace App\Core;
use App\Core\db\Database;
use App\Core\db\DbModel;

class Application
{
    public static $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public Controller $controller;
    public Database $db;
    public static Application $app;
    public string $userClass;
    public Session $session;
    public ?DbModel $user;
    public $layout = 'main';
    public const EVENT_BEFORE_REQUEST = 'before_request';
    public const EVENT_AFTER_REQUEST = 'after_request';

    protected array $eventListeners = [];

    public function __construct(
        string $rootPath,
        array $config,
        public ?View $view = null
    )
    {
        self::$ROOT_DIR = $rootPath;
        $this->response = new Response();
        $this->request = new Request();
        $this->router = new Router($this->request,$this->response);
        $this->db = new Database($config['db']);
        $this->session = new Session();
        self::$app = $this;
        $this->userClass = $config['userClass'];

        /** @var DbModel $primaryValue */
        $primaryValue = $this->session->get('user');
        if ($primaryValue){
            $primaryKey = $this->userClass::primaryKey();
            $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }

    public function run()
    {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
        try {
            $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error',[
                'exception' => $e
            ]);
        }
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public static function isGuest()
    {
        return ! self::$app->user;
    }

    public function login(UserModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function on($eventName, $callback)
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    public function triggerEvent($eventName)
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}