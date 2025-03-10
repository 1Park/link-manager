<?php

declare(strict_types=1);

namespace OCA\LinkManager\Controller;

use OCA\LinkManager\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Share\IManager as IShareManager;
use OCP\Share\IShare;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;

class PageController extends Controller
{
    private IUserSession $userSession;
    private IShareManager $shareManager;
    private IURLGenerator $urlGenerator;
    private LoggerInterface $logger;
    protected $request;

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        IShareManager $shareManager,
        IURLGenerator $urlGenerator,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->shareManager = $shareManager;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
        $this->request = $request;
    }

    #[NoCSRFRequired]
    #[NoAdminRequired]
    #[FrontpageRoute(verb: 'GET', url: '/')]
    public function index(?string $getParameter): TemplateResponse
    {
        $this->logger->debug('PageController::index called', ['app' => 'linkmanager']);

        if ($getParameter === null) {
            $getParameter = "";
        }

        $sharedItems = [];
        $user = $this->userSession->getUser();

        if ($user === null) {
            $this->logger->warning('No user logged in', ['app' => 'linkmanager']);
            return new TemplateResponse(
                'linkmanager',
                'index',
                ['myMessage' => $getParameter, 'sharedItems' => [], 'error' => 'Please log in to see your shared files.']
            );
        }

        try {
            $shares = $this->shareManager->getSharesBy($user->getUID(), IShare::TYPE_LINK);
            $this->logger->debug('Public link shares retrieved: ' . count($shares), ['app' => 'linkmanager']);

            if (empty($shares)) {
                $this->logger->info('No public link shares found for user: ' . $user->getUID(), ['app' => 'linkmanager']);
            } else {
                foreach ($shares as $share) {
                    $shareId = $share->getId();
                    $shareType = $share->getShareType();
                    $this->logger->debug("Processing share ID: $shareId, Type: $shareType", ['app' => 'linkmanager']);

                    $node = $share->getNode();
                    if ($node === null) {
                        $this->logger->warning("Node is null for share ID: $shareId", ['app' => 'linkmanager']);
                        continue;
                    }

                    $path = $node->getPath();
                    $this->logger->debug("Share path: $path", ['app' => 'linkmanager']);

                    if (!isset($sharedItems[$path])) {
                        $token = $share->getToken();
                        // PHP $_SERVER로 도메인과 프로토콜 가져오기
                        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
                        $protocol = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
                        $baseUrl = "$protocol://$host";
                        $shareUrl = $baseUrl . '/index.php/s/' . $token;
                        $sharedItems[$path] = [
                            'path' => $path,
                            'type' => $node->getType(),
                            'externalURL' => $shareUrl,
                        ];
                        $this->logger->debug("External URL set: " . $sharedItems[$path]['externalURL'], ['app' => 'linkmanager']);
                        $this->logger->debug("Host: $host, Protocol: $protocol", ['app' => 'linkmanager']);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving shares: ' . $e->getMessage(), ['app' => 'linkmanager']);
            return new TemplateResponse(
                'linkmanager',
                'index',
                ['myMessage' => $getParameter, 'sharedItems' => [], 'error' => 'Error loading shared files: ' . $e->getMessage()]
            );
        }

        $this->logger->debug('Final shared items count: ' . count($sharedItems), ['app' => 'linkmanager']);
        return new TemplateResponse(
            'linkmanager',
            'index',
            [
                'myMessage' => $getParameter,
                'sharedItems' => array_values($sharedItems),
            ],
            //'',
            //['css' => ['linkmanager/style']] // CSS 파일 추가
        );
    }
}

