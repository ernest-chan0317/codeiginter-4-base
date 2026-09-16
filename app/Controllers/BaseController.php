<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BaseModel;
use CodeIgniter\Controller;
use CodeIgniter\Model;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;

abstract class BaseController extends Controller
{
    protected Model $model;

    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    protected $helpers = [];

    protected function baseIndex(): array
    {
        $pageSize = (int) ($this->request->getVar('pageSize') ?? 10);
        $pageSize = max(1, min($pageSize, 100));

        $page       = (int) ($this->request->getVar('page') ?? 0);
        $sortBy     = $this->request->getVar('sortBy');
        $activeOnly = $this->request->getVar('activeOnly');
        $model      = $this->model;

        if ($activeOnly && $model instanceof BaseModel && $model->hasActiveColumn()) {
            $model = $model->where('active', true);
        }

        $model = $this->applyIndexFilters($model);

        if ($sortBy && $model instanceof BaseModel && $model->isSortable((string) $sortBy)) {
            $order = strtolower((string) ($this->request->getVar('orderBy') ?? 'asc'));
            $order = in_array($order, ['asc', 'desc'], true) ? $order : 'asc';
            $model = $model->orderBy((string) $sortBy, $order);
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

    protected function applyIndexFilters(Model $model): Model
    {
        foreach ($this->getIndexFilterMap() as $param => $column) {
            $value = $this->request->getVar($param);

            if ($value !== null && $value !== '') {
                $model = $model->where($column, $value);
            }
        }

        return $model;
    }

    /**
     * @return array<string, string> queryParam => dbColumn
     */
    protected function getIndexFilterMap(): array
    {
        return property_exists($this, 'indexFilters') ? $this->indexFilters : [];
    }

    protected function baseCreate(): int|false
    {
        $data = $this->request->getJSON(true) ?? [];
        $id   = $this->model->insert($data);

        return $id === false ? false : (int) $id;
    }

    protected function baseUpdate(int $id): bool
    {
        $data = $this->request->getJSON(true) ?? [];

        return $this->model->update($id, $data);
    }

    protected function baseDelete(int $id): bool
    {
        return $this->model->delete($id);
    }
}
