<?php namespace Devise\Pages;

use Devise\Support\Framework;
use Illuminate\Routing\Controller;

/**
 * All pages registered in dvs_pages database table
 * come through this controller show method. The reason
 * for this is so that the database can be in charge of
 * the routes and a non developer can construct new
 * pages with predefined templates in a dropdown selectbox.
 * The templates have already been designed by the developer
 * but new pages can be added easily by the cms administrator.
 */
class PagesController extends Controller
{
  /**
   * Repository for retrieving pages
   *
   * @var PagesRepository
   */
  protected $PagesRepository;

  /**
   * Creates a new DvsPagesController instance.
   *
   * @param  PagesRepository $PagesRepository
   * @param Framework $Framework
   */
  public function __construct(PagesRepository $PagesRepository, Framework $Framework)
  {
    $this->PagesRepository = $PagesRepository;

    $this->Redirect = $Framework->Redirect;
    $this->View = $Framework->View;
    $this->Request = $Framework->Request;
    $this->Route = $Framework->Route;
  }

  /**
   * Displays details of a page
   *
   * @return Response
   */
  public function show()
  {
    // @todo rename var and check user permissions
    $editing = true;

    $pageVersionHash = $this->Request->get('page_version_share', null);
    $pageVersionName = $this->Request->get('page_version', null);

    $page = $pageVersionHash
      ? $this->PagesRepository->findByRouteNameAndPreviewHash($this->Route->currentRouteName(), $pageVersionHash)
      : $this->PagesRepository->findByRouteName($this->Route->currentRouteName(), $pageVersionName, $editing);

    $page = $this->PagesRepository->getTranslatedVersions($page);

    $localized = $this->PagesRepository->findLocalizedPage($page);
    $localized = $this->PagesRepository->getTranslatedVersions($localized);

    return $localized ? $this->retrieveLocalRedirect($localized) : $this->retrieveResponse($page);
  }

  /**
   * This retrieves a page with all the
   * view's vars set on the response
   *
   * @param \DvsPage $page
   * @throws PagesException
   */
  public function retrieveResponse($page)
  {
    $route = $this->Route->getCurrentRoute();

    $data['page'] = $page;
    $data['input'] = $this->Request->all();
    $data['params'] = $route ? $route->parameters() : [];

//    $this->DataBuilder->setData($data);

    return $this->getResponse($page);
  }

  /**
   * This retrieves the a redirect for the user's language
   *
   * @param \DvsPage $localized
   * @throws PagesException
   */
  public function retrieveLocalRedirect($localized)
  {
    $route = $this->Route->getCurrentRoute();
    $params = $route ? $route->parameters() : [];

    return $this->Redirect->route($localized->route_name, $params);
  }

  /**
   * Gets the response for this page
   *
   * @param \DvsPage $page
   * @throws PagesException
   */
  protected function getResponse($page)
  {
    if (strtolower($page->response_type) == 'view')
    {
      return $this->getView($page);
    }

    if (strtolower($page->response_type) == 'function')
    {
      return $this->getFunction($page);
    }

    throw new PagesException("Unknown response type [" . $page->response_type . "]");
  }

  /**
   * Get the results from a function as the response type
   *
   * @param \DvsPage $page
   * @return array
   * @throws Viewvars\DeviseRouteConfigurationException
   */
  protected function getFunction($page)
  {
    $config = ($page->response_params != null && $page->response_params != '')
      ? [$page->response_path => explode(',', $page->response_params)]
      : $page->response_path;

    if (isset($config[$page->response_path]))
    {
      // wrapping params in curly braces
      foreach ($config[$page->response_path] as &$param)
      {
        $param = '{' . $param . '}';
      }
    }

//    return $this->DataBuilder->getValue($config);
  }

  /**
   * Gets a view as the response type
   *
   * @param $page
   * @return mixed
   */
  protected function getView($page)
  {
    $pageData = $this->DataBuilder->getData();

    // allow a page version to override the page view
    $view = $page->version->view ?: $page->view;

    return $this->View->make($view, $pageData);
  }
}
