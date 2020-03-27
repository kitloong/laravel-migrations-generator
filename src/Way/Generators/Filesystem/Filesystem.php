<?php namespace Way\Generators\Filesystem;

class Filesystem
{
    /**
     * Make a file
     *
     * @param  string  $file
     * @param  string  $content
     * @return false|int
     * @throws FileAlreadyExists
     */
    public function make(string $file, string $content)
    {
        if ($this->exists($file)) {
            throw new FileAlreadyExists;
        }

        return file_put_contents($file, $content);
    }

    /**
     * Determine if file exists
     *
     * @param  string  $file
     * @return bool
     */
    public function exists(string $file)
    {
        return file_exists($file);
    }

    /**
     * Fetch the contents of a file
     *
     * @param  string  $file
     * @return string
     * @throws FileNotFound
     */
    public function get(string $file)
    {
        if (!$this->exists($file)) {
            throw new FileNotFound($file);
        }

        return file_get_contents($file);
    }
}
