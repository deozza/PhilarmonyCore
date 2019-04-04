<?php

namespace Deozza\PhilarmonyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RuleManager
{
    public function __construct(EntityManagerInterface $entityManager, DatabaseSchemaLoader $schemaLoader)
    {
        $this->em = $entityManager;
        $this->schemaLoader = $schemaLoader;
    }

    public function decide($object,Request $request, $folder)
    {
        $errors = [];

        foreach (glob($folder.'/../Rules/*Rule.php') as $file)
        {
            $class = $this->getClassNamespaceFromFile($file);

            if(!empty($class))
            {
                $class = "\\$class\\".basename($file, ".php");

                $rule = new $class;

                if($rule->supports($object, $request))
                {
                    $error = $rule->decide($object,$request, $this->em, $this->schemaLoader);
                    if(!empty($error))
                    {
                        $errors[] = $error;
                    }
                }
            }
        }

        if(count($errors) > 0)
        {
            return $errors;
        }

        return count($errors);
    }

    protected function getClassNamespaceFromFile($filePathName) : string
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        } else {
            return $namespace;
        }
    }
}