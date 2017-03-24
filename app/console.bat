@ECHO OFF

REM set default path to php.exe
SET PHP_EXEC=php.exe

REM search for default php path
IF NOT ""=="%PHP_HOME%" SET PHP_EXEC=%PHP_HOME%\php.exe

REM call console script with php
"%PHP_EXEC%" -d register_globals=Off -f "%~dp0\\console" -- %*