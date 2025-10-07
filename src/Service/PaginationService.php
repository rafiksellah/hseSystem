<?php

namespace App\Service;

class PaginationService
{
    public function paginate(array $items, int $page = 1, int $limit = 10): array
    {
        $totalItems = count($items);
        $totalPages = (int) ceil($totalItems / $limit);
        
        // Validation des paramètres
        $page = max(1, min($page, $totalPages));
        $limit = max(1, min($limit, 100)); // Limite maximale de 100
        
        // Calculer l'offset
        $offset = ($page - 1) * $limit;
        
        // Extraire les éléments pour la page courante
        $paginatedItems = array_slice($items, $offset, $limit);
        
        return [
            'items' => $paginatedItems,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $limit,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
                'startItem' => $offset + 1,
                'endItem' => min($offset + $limit, $totalItems)
            ]
        ];
    }

    public function paginateQueryBuilder($queryBuilder, int $page = 1, int $limit = 10): array
    {
        // Compter le total d'éléments
        $countQuery = clone $queryBuilder;
        $totalItems = $countQuery->select('COUNT(DISTINCT ' . $countQuery->getRootAliases()[0] . ')')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = (int) ceil($totalItems / $limit);
        
        // Validation des paramètres
        $page = max(1, min($page, $totalPages));
        $limit = max(1, min($limit, 100));
        
        // Calculer l'offset
        $offset = ($page - 1) * $limit;
        
        // Exécuter la requête avec pagination
        $items = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $limit,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
                'startItem' => $offset + 1,
                'endItem' => min($offset + $limit, $totalItems)
            ]
        ];
    }

    public function getPaginationFromRequest(array $requestParams): array
    {
        $page = max(1, (int) ($requestParams['page'] ?? 1));
        $limit = max(1, min(100, (int) ($requestParams['limit'] ?? 10)));
        
        return [
            'page' => $page,
            'limit' => $limit
        ];
    }

    public function getPaginationInfo(array $pagination): array
    {
        return [
            'currentPage' => $pagination['currentPage'],
            'totalPages' => $pagination['totalPages'],
            'totalItems' => $pagination['totalItems'],
            'itemsPerPage' => $pagination['itemsPerPage'],
            'hasNextPage' => $pagination['hasNextPage'],
            'hasPreviousPage' => $pagination['hasPreviousPage'],
            'startItem' => $pagination['startItem'],
            'endItem' => $pagination['endItem']
        ];
    }
}
