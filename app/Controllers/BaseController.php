<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Model;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected Model $model;

    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    protected $helpers = [];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    public function baseIndex(Model $model): array
    {
        $pageSize   = $this->request->getVar('pageSize') ?? 1;
        $page       = (int) ($this->request->getVar('page') ?? 0);
        $sortBy     = $this->request->getVar('sortBy');
        $activeOnly = $this->request->getVar('activeOnly');

        $model->where($model->table . '.deleted_at', null);

        if ($activeOnly) {
            $model = $model->where('active', true);
        }

        if ($sortBy) {
            $model = $model->orderBy($sortBy, $this->request->getVar('orderBy'));
        }

        $data  = $model->paginate($pageSize, 'default', $page + 1);
        $pager = $model->pager;

        return [
            'data'        => $data,
            'totalPage'   => $pager->getPageCount(),
            'total'       => $pager->getTotal(),
            'currPage'    => $pager->getCurrentPage(),
            'prePage'     => $pager->getCurrentPage() > 1 ? $pager->getCurrentPage() - 1 : null,
            'nextPage'    => $pager->getCurrentPage() < $pager->getPageCount() ? $pager->getCurrentPage() + 1 : null,
            'prePageUrl'  => $pager->getPreviousPageURI(),
            'nextPageUrl' => $pager->getNextPageURI(),
            'firstPage'   => $pager->getFirstPage(),
            'lastPage'    => $pager->getLastPage(),
        ];
    }

    public function baseCreate(): int
    {
        $data               = $this->request->getJSON(true);
        $data['created_by'] = auth()->user()->username;

        return (int) $this->model->insert($data);
    }

    public function baseUpdate(int $id): void
    {
        $data               = $this->request->getJSON(true);
        $data['updated_by'] = auth()->user()->username;

        $this->model->update($id, $data);
    }

    public function baseDelete(int $id): void
    {
        $this->model->delete($id);
        $this->model->update($id, ['deleted_by' => auth()->user()->username]);
    }
}
