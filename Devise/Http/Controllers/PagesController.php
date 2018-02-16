<?php

namespace Devise\Http\Controllers;

use Devise\Http\Requests\ApiRequest;
use Devise\Http\Requests\Pages\CopyPage;
use Devise\Http\Requests\Pages\StorePage;
use Devise\Http\Requests\Pages\UpdatePage;
use Devise\Http\Resources\Api\PageResource;
use Devise\Pages\PagesManager;
use Devise\Pages\PagesRepository;
use Devise\Sites\SiteDetector;
use Devise\Support\Framework;

use Illuminate\Routing\Controller;

class PagesController extends Controller
{
  protected $PagesRepository;

  private $PagesManager;

  private $SiteDetector;

  private $Redirect;

  private $View;

  private $Request;

  private $Route;

  /**
   * Creates a new DvsPagesController instance.
   *
   * @param  PagesRepository $PagesRepository
   * @param Framework $Framework
   */
  public function __construct(PagesRepository $PagesRepository, PagesManager $PagesManager, SiteDetector $SiteDetector, Framework $Framework)
  {
    $this->PagesRepository = $PagesRepository;
    $this->PagesManager = $PagesManager;
    $this->SiteDetector = $SiteDetector;

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
  public function show(ApiRequest $request)
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

    if ($localized)
    {
      $route = $this->Route->getCurrentRoute();
      $params = $route ? $route->parameters() : [];

      return $this->Redirect->route($localized->route_name, $params);
    } else
    {
      $page->version->load('slices.slice', 'slices.fields');

      $page->version->registerComponents();

      return $this->View->make($page->version->template->layout, ['page' => $page]);
    }
  }

  /**
   * Request the page listing
   *
   */
  public function pages(ApiRequest $request)
  {
    $site = $this->SiteDetector->current();

    $defaultLanguage = $site->default_language;

    $languageId = $request->input('language_id', $defaultLanguage->id);

    $pages = $this->PagesRepository->pages($site->id, $languageId);

    return PageResource::collection($pages);
  }

  /**
   * Request the page listing
   *
   */
  public function suggestList(ApiRequest $request)
  {
    $term = $request->input('term');

    return $this->PagesRepository->getPagesList($term);
  }

  /**
   * Request a new page be created
   *
   * @param StorePage $request
   * @return PageResource
   */
  public function store(StorePage $request)
  {
    $site = $this->SiteDetector->current();

    $defaultLanguage = $site->default_language;

    $input = $request->all();
    $input['site_id'] = $site->id;
    $input['language_id'] = $request->input('language_id', $defaultLanguage->id);

    $page = $this->PagesManager->createNewPage($input);

    return new PageResource($page);
  }

  /**
   * Request page be updated with given input
   *
   * @param UpdatePage $request
   * @param  integer $id
   * @return PageResource
   */
  public function update(UpdatePage $request, $id)
  {
    $page = $this->PagesManager->updatePage($id, $request->all());

    $page->load('versions','metas');

    return new PageResource($page);
  }

  /**
   * Request page be copied
   *
   * @param CopyPage $request
   * @param  integer $id
   * @return PageResource
   */
  public function copy(CopyPage $request, $id)
  {
    $page = $this->PagesManager->copyPage($id, $request->all());

    $page->load('versions','metas');

    return new PageResource($page);
  }

  /**
   * ApiRequest the page be copied to another page (duplicated)
   *
   * @param ApiRequest $request
   * @param  integer $id
   * @return PageResource
   * @todo make this onework
   */
  public function requestCopyPage(ApiRequest $request, $id)
  {
    $page = $this->PagesManager->copyPage($id, $request->all());

    return new PageResource($page);
  }

  /**
   * Request the page be deleted from database
   *
   * @param ApiRequest $request
   * @param  integer $id
   * @return PageResource
   */
  public function delete(ApiRequest $request, $id)
  {
    $this->PagesManager->destroyPage($id);
  }
}
