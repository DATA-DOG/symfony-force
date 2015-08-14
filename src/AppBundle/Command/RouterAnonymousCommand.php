<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RouterAnonymousCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('router:anonymous')
            ->setDescription('Shows all anonymously accessible routes..')
            ->setHelp(<<<EOF
The <info>%command.name%</info> Shows all anonymously accessible routes..

<info>php %command.full_name%</info>
<info>php %command.full_name% --env=prod</info>
EOF
        );
    }

    private function isAccessedAnonymously($routeName, Route $route)
    {
        if (!in_array('GET', $route->getMethods()) && $route->getMethods()) {
            return false; // GET method must be allowed
        }

        if (strpos($routeName, '_') === 0) {
            return false; // internal|private routes
        }

        $compiled = $route->compile();
        $params = [];
        foreach ($compiled->getPathVariables() as $key) {
            $params[$key] = 'any'; // we do not care about it
        }
        foreach ($route->getRequirements() as $key => $regex) {
            $params[$key] = 'any'; // we do not care about it
        }
        foreach ($route->getDefaults() as $key => $default) {
            $params[$key] = $default;
        }
        if (!array_key_exists('_controller', $params)) {
            return false; // route is dynamic, should not be index by robots
        }

        $uri = $this->get('router')->generate($routeName, $params);

        // mock the request
        $request = Request::create('http://mock.com' . $uri);
        $request->setSession(new Session(new MockFileSessionStorage()));

        // run the request through security firewall
        $event = new GetResponseEvent(
            $this->getApplication()->getKernel(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        try {
            $this->get('security.firewall')->onKernelRequest($event);
        } catch (AccessDeniedException $e) {
            return false; // access is denied
        }

        return !$event->getResponse() instanceof RedirectResponse;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // only anonymous user access
        $this->get('security.context')->setToken(new AnonymousToken('dev', 'anon.', []));

        $router = $this->get('router');
        $anonymous = [];
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            if ($this->isAccessedAnonymously($name, $route)) {
                $anonymous[$name] = $route;
            }
        }

        $rows = [];
        foreach ($anonymous as $name => $route) {
            $rows[] = [$name, $route->getPattern()];
        }
        $this
            ->getHelper('table')
            ->setHeaders(['Route', 'Path'])
            ->setRows($rows)
            ->render($output);
    }

    /**
     * @param string $service
     */
    private function get($service)
    {
        return $this->getContainer()->get($service);
    }
}
