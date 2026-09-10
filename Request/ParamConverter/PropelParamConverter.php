<?php

namespace Propel\Bundle\PropelBundle\Request\ParamConverter;

use Propel\Bundle\PropelBundle\Util\PropelInflector;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * PropelParamConverter
 *
 * This convert action parameter to a Propel Object
 * there is two option for this converter:
 *
 * mapping : take an array of routeParam => column
 * exclude : take an array of routeParam to exclude from the conversion process
 *
 *
 * @author     Jérémie Augustin <jeremie.augustin@pixel-cookers.com>
 */
class PropelParamConverter implements ValueResolverInterface
{
    /**
     * the pk column (e.g. id)
     * @var string
     */
    protected $pk;

    /**
     * list of column/value to use with filterBy
     * @var array<string, string>
     */
    protected array $filters = array();

    /**
     * list of route parameters to exclude from the conversion process
     * @var string[]
     */
    protected array $exclude = array();

    /**
     * list of with option use to hydrate related object
     * @var array<string|array<array-key, string>>
     */
    protected array $withs;

    /**
     * name of method use to call a query method
     * @var string
     */
    protected $queryMethod;

    /**
     * @var bool
     */
    protected $hasWith = false;

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return iterable
     *
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) {
            return array();
        }

        $class = $argument->getType();
        $classQuery = $class . 'Query';
        $classTableMap = $class::TABLE_MAP;
        $this->filters = array();
        $this->exclude = array();

        if (!class_exists($classQuery)) {
            throw new \Exception(sprintf('The %s Query class does not exist', $classQuery));
        }

        $tableMap = new $classTableMap();
        $pkColumns = $tableMap->getPrimaryKeys();

        if (count($pkColumns) === 1) {
            $pk = array_pop($pkColumns);
            $this->pk = strtolower($pk->getName());
        }

        $options = array();

        // Check request attributes for converter options, if there are non provided.
        if (empty($options) && $request->attributes->has('propel_converter')) {
            $converterOption = $request->attributes->get('propel_converter');
            if (!empty($converterOption[$argument->getName()])) {
                $options = $converterOption[$argument->getName()];
            }
        }
        if (isset($options['mapping'])) {
            // We use the mapping for calling findPk or filterBy
            foreach ($options['mapping'] as $routeParam => $column) {
                if ($request->attributes->has($routeParam)) {
                    if ($this->pk === $column) {
                        $this->pk = $routeParam;
                    } else {
                        $this->filters[$column] = $request->attributes->get($routeParam);
                    }
                }
            }
        } else {
            $this->exclude = isset($options['exclude']) ? $options['exclude'] : array();
            $this->filters = $request->attributes->all();
        }

        if (array_key_exists($argument->getName(), $this->filters)) {
            unset($this->filters[$argument->getName()]);
        }

        $this->withs = isset($options['with']) ? is_array($options['with']) ? $options['with'] : array($options['with']) : array();

        $this->queryMethod = $queryMethod = isset($options['query_method']) ? $options['query_method'] : null;

        if (null !== $this->queryMethod && method_exists($classQuery, $this->queryMethod)) {
            // find by custom method
            $query = $this->getQuery($classQuery);
            // execute a custom query
            $object = $query->$queryMethod($request->attributes);
        } else {
            // find by Pk
            if (false === $object = $this->findPk($classQuery, $request)) {
                // find by criteria
                if (false === $object = $this->findOneBy($classQuery, $request)) {
                    if ($argument->isNullable()) {
                        //we find nothing but the object is optional
                        $object = null;
                    } else {
                        throw new \LogicException('Unable to guess how to get a Propel object from the request information.');
                    }
                }
            }
        }

        if (null === $object && false === $argument->isNullable()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($argument->getName(), $object);

        return array($object);
    }

    /**
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(ArgumentMetadata $argument): bool
    {
        $classname = $argument->getType();
        if (!$classname) {
            return false;
        }

        if (!class_exists($classname)) {
            return false;
        }

        // Propel Class?
        $class = new \ReflectionClass($classname);
        if ($class->implementsInterface('\Propel\Runtime\ActiveRecord\ActiveRecordInterface')) {
            return true;
        }

        return false;
    }

    /**
     * Init the query class with optional joinWith
     *
     * @param string $classQuery
     *
     * @return ModelCriteria
     *
     * @throws \Exception
     */
    protected function getQuery(string $classQuery): ModelCriteria
    {
        $query = $classQuery::create();

        foreach ($this->withs as $with) {
            if (is_array($with)) {
                if (2 == count($with)) {
                    $query->joinWith($with[0], $this->getValidJoin($with));
                    $this->hasWith = true;
                } else {
                    throw new \Exception(sprintf('ParamConverter : "with" parameter "%s" is invalid,
                            only string relation name (e.g. "Book") or an array with two keys (e.g. {"Book", "LEFT_JOIN"}) are allowed',
                        var_export($with, true)));
                }
            } else {
                $query->joinWith($with);
                $this->hasWith = true;
            }
        }

        return $query;
    }

    /**
     * Return the valid join Criteria base on the with parameter
     *
     * @param string[] $with
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getValidJoin(array $with): string
    {
        switch (trim(str_replace(array('_', 'JOIN'), '', strtoupper($with[1])))) {
            case 'LEFT':
                return Criteria::LEFT_JOIN;
            case 'RIGHT':
                return Criteria::RIGHT_JOIN;
            case 'INNER':
                return Criteria::INNER_JOIN;
        }

        throw new \Exception(sprintf('ParamConverter : "with" parameter "%s" is invalid,
                only "left", "right" or "inner" are allowed for join option',
            var_export($with, true)));
    }

    /**
     * Try to find the object with the id
     *
     * @param string $classQuery the query class
     * @param Request $request
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function findPk(string $classQuery, Request $request)
    {
        if (in_array($this->pk, $this->exclude) || !$request->attributes->has($this->pk)) {
            return false;
        }

        $query = $this->getQuery($classQuery);

        if (!$this->hasWith) {
            return $query->findPk($request->attributes->get($this->pk));
        } else {
            return $query->filterByPrimaryKey($request->attributes->get($this->pk))->find()->getFirst();
        }
    }

    /**
     * Try to find the object with all params from the $request
     *
     * @param string $classQuery the query class
     * @param Request $request
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function findOneBy($classQuery, Request $request)
    {
        $query = $this->getQuery($classQuery);
        $hasCriteria = false;
        foreach ($this->filters as $column => $value) {
            if (!in_array($column, $this->exclude)) {
                try {
                    $query->{'filterBy' . PropelInflector::camelize($column)}($value);
                    $hasCriteria = true;
                } catch (\Exception $e) {
                }
            }
        }

        if (!$hasCriteria) {
            return false;
        }

        if (!$this->hasWith) {
            return $query->findOne();
        } else {
            return $query->find()->getFirst();
        }
    }
}
