<?php

namespace App\Tools;

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class DataTable
 *
 * @package App\Tools
 */
class DataTable
{
    /**
     * @param QueryBuilder       $queryBuilder
     * @param PaginatorInterface $paginator
     * @param                    $columns
     * @param                    $pageSize
     * @param                    $start
     * @param string             $orders
     * @param                    $columnsFilters
     * @param                    $filters
     * @param null               $rowPrefix
     *
     * @return array
     */
    public function generateTable(
        QueryBuilder $queryBuilder,
        PaginatorInterface $paginator,
        $columns,
        $pageSize,
        $start,
        $orders,
        $columnsFilters,
        $filters,
        $rowPrefix = null
    )
    {
        // Convert $columns workshopciate array to numeric array
        $cols = array();
        foreach ($columns as $column) {
            $cols[] = $column;
        }

        // Is There global filters
        if (is_array($filters) && count($filters)) {
            $counter = 10000;
            foreach ($filters as $keyFilter => $valueFilter) {
                if (is_array($valueFilter) && count($valueFilter)) {
                    $parameter = [];
                    $orx = $queryBuilder->expr()->orx();
                    foreach ($valueFilter as $f) {
                        if (!is_array($columns[$keyFilter]['id'])) {
                            if ($f === 'null') {
                                $orx->add($columns[$keyFilter]['id'] . ' IS NULL');
                            } else {
                                $orx->add($queryBuilder->expr()->eq($columns[$keyFilter]['id'], '?' . $counter));
                                $parameter[$counter] = $f;
                                $counter++;
                            }
                        }
                    }
                    $queryBuilder->andWhere($orx);
                    foreach ($parameter as $keyParam => $valueParam) {
                        $queryBuilder->setParameter($keyParam, $valueParam);
                    }
                }
            }
        }

        // Is There an order to apply
        if (is_array($orders) && count($orders)) {
            foreach ($orders as $order) {
                if (isset($order['column']) && isset($cols[$order['column']])) {
                    if (is_array($cols[$order['column']]['id'])) {
                        foreach ($cols[$order['column']]['id'] as $colId) {
                            $queryBuilder->addOrderBy($colId, $order['dir']);
                        }
                    } else {
                        $queryBuilder->addOrderBy($cols[$order['column']]['id'], $order['dir']);
                    }
                }
            }
        }

        if (!$pageSize) {
            $pageSize = 50;
        }
        
        if (!$start) {
            $start = 1;
        } else {
            $start = ceil(($start + 1) / $pageSize);
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $start,
            $pageSize,
            ['wrap-queries' => true]
        );

        $recordsTotal = $pagination->getTotalItemCount();
        $records = $pagination;

        if ($rowPrefix === null) {
            $rowPrefix = 'row_';
        }

        // Format Datas
        $data = array();
        foreach ($records as $record) {
            $tmp = array();
            if (is_array($record)) {
                $tmp['DT_RowId'] = $rowPrefix . $record['id'];
                $tmp['DT_RowData']['pkey'] = $record['id'];
            } else {
                $tmp['DT_RowId'] = $rowPrefix . $record->getId();
                $tmp['DT_RowData']['pkey'] = $record->getId();
            }

            foreach ($columns as $c) {
                $o = '';
                if (isset($c['method']) && is_array($c['method']) && count($c['method'])) {
                    $r = $record;
                    foreach ($c['method'] as $method) {
                        if ($r && is_array($r)) {
                            $r = $r[$method];
                        } else if ($r && !is_array($r)) {
                            if (is_array($method) && isset($method[0])) {
                                // Utilisé pour les tables associative par exemple lorsque l'on veut faire ca : $item->getActivies()[0],
                                // on met donc un array avec getActivities en première case et 0 en deuxième case
                                $r = $r->{$method[0]}()[$method[1]];
                            } else {
                                $r = $r->$method();
                            }
                        } else {
                            $r = '';
                        }
                    }
                    $o = $r;
                }

                if (isset($c['filter']) && is_array($c['filter']) && count($c['filter'])) {
                    foreach ($c['filter'] as $filter) {
                        if (is_object($o) && $o !== null) {
                            $o = call_user_func_array(array($o, $filter['name']), $filter['args']);
                        } else if ($o !== null) {
                            if (isset($filter['args']) && is_array($filter['args'])) {
                                $o = call_user_func_array($filter['name'], array_merge(array($o), $filter['args']));
                            } else {
                                $o = call_user_func_array($filter['name'], array($o));
                            }
                        }
                    }
                }

                $tmp[$c['label']] = $o;
            }
            $data[] = $tmp;
        }

        return array(
            'recordsTotal' => $recordsTotal,
            'data' => $data
        );
    }

}
