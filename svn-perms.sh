#!/bin/bash
# 
# Script to override the default SVN command and pass all parameters to the "proper"
# svn command while running a few operations after the svn command has run.
# 
# To install:
#   as root:
#      1. move this script to another location on the server so that it can be customized
#      2. edit the paths for REAL_SVN and WORDPRESS_PATH to reflect the server setup
#      3. make sure this file is editable with chmod +x svn-perms.sh
#   as normal user:
#      4. edit the ~/.bashrc file and add a new line:
#           alias svn="/path/to/this/file $@"
# 
# Usage
#
#   Usage should not vary from any other SVN command. Call svn as normal and all svn commands will
#   be passed through to the normal svn executable. SVN will respond as normal but the commands also
#   listed in this file will be executed on update commands.
# 
#   This will work in the scope of the current user. Someone doing updates as root will have the same 
#   effect on the editability of files as with normal SVN operation.

REAL_SVN='/usr/bin/svn';
WORDPRESS_PATH='/path/to/web/root/';

$REAL_SVN $@;

if [ $1 = 'up' ]; then
	find -L $WORDPRESS_PATH -type f -name 'custom.css' -exec bash -c 'chmod 0777 $0' '{}' \;
fi