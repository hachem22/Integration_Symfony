<?php

namespace App\Repository;

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    /**
     * Get the latest blog posts (order by creation date, descending)
     *
     * @param int $limit The number of latest blogs to retrieve
     * @return Blog[] Returns an array of Blog objects
     */
    public function findLatestBlogs(int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC') // Make sure you have a 'createdAt' field or adjust as needed
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find blogs by category
     *
     * @param string $category The category to filter blogs by
     * @return Blog[] Returns an array of Blog objects
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.category = :category') // Adjust this to match your actual category field
            ->setParameter('category', $category)
            ->orderBy('b.createdAt', 'DESC') // Optionally order by creation date
            ->getQuery()
            ->getResult();
    }
}


