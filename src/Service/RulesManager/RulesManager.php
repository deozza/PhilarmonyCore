<?php

namespace Deozza\PhilarmonyBundle\Service\RulesManager;

use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;

class RulesManager
{
    public function __construct(EntityManagerInterface $entityManager, DatabaseSchemaLoader $schemaLoader, $srcPath)
    {
        $this->em = $entityManager;
        $this->schemaLoader = $schemaLoader;
        $this->srcPath = $srcPath;
        $this->folders = [];
    }

    public function decideConflict($object, $posted, $method, $folder)
    {
        $this->getUsefullFolder($this->srcPath);
        $this->folders[] = [$folder."/../Rules"];
        $errors = $this->decide($object, $posted, $method, $this->folders, $glob = "/*ConflictRule.php");

        if(count($errors) > 0)
        {
            return $errors;
        }

        return count($errors);
    }

    protected function decide($object, $posted, $method, $folders, $glob)
    {
        $errors = [];
        foreach($folders as $folder)
        {
            foreach (glob($folder[0].$glob) as $file)
            {
                $class = $this->getClassNamespaceFromFile($file);

                if(!empty($class))
                {
                    $class = "\\$class\\".basename($file, ".php");

                    $rule = new $class;

                    if($rule->supports($object, json_decode($posted, true), $method))
                    {
                        $error = $rule->decide($object, json_decode($posted, true), $method, $this->em, $this->schemaLoader);
                        if(!empty($error))
                        {
                            $errors[] = $error;
                        }

                    }
                }
            }
        }
        return $errors;

    }

    protected function getUsefullFolder($path)
    {
        if(glob($path."/*Rules"))
        {
            $this->folders[] = glob($path."*Rules");
        }
        foreach(scandir($path) as $subfolder)
        {
            if(preg_match("/^(\w+)$/", $subfolder))
            {
                $this->getUsefullFolder($path.$subfolder."/");
            }
        }
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