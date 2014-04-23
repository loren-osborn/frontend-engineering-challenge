Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }
 
exec { 'apt-get update':
  command => 'apt-get update',
  timeout => 60,
  tries   => 3
} -> Package <| |>
 
class { 'apt':
  always_apt_update => true,
}
 
package { ['python-software-properties']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}
 
$sysPackages = [ 'build-essential', 'git', 'curl', 'bc', 'acl', 'sqlite3']
package { $sysPackages:
  ensure => "installed",
  require => Exec['apt-get update'],
}

class { "apache": }
 
apache::module { 'rewrite': }
 
apache::vhost { 'default':
  docroot                  => '/vagrant/project/web',
  directory                  => '/vagrant/project/web',
  directory_allow_override => "All \nRequire all granted",
  priority                 => '000',
  server_name              => false,
  template                 => 'apache/virtualhost/vhost.conf.erb',
}
 
apt::ppa { 'ppa:ondrej/php5':
  before  => Class['php'],
}
 
class { 'php':
	# config_file => '/etc/php5/apache2/conf.d/php.ini'
}
 
$phpModules = [ 'imagick', 'xdebug', 'curl', 'mysql', 'cli', 'intl', 'mcrypt', 'memcache', 'sqlite']
 
php::module { $phpModules: }
 
php::ini { 'php':
  value   => ['date.timezone = "America/Los_Angeles"', 'xdebug.max_nesting_level = 250' ],
  target  => 'php.ini',
  service => 'apache'
}

class { "mysql":
  root_password => 'v4ppNHWk7YJ5PKH4NDs5Pqdm',
}

mysql::grant { 'lso_cnfc':
  mysql_privileges => 'ALL',
  mysql_password => 'WERgXwrLBVaYgMWxkJdP8c5',
  mysql_db => 'lso_cnfc',
  mysql_user => 'lso_cnfc_user',
  mysql_host => 'localhost'
  # ,
  # mysql_db_init_query_file => '/full/path/to/the/schema.sql',
}

class composer {
    exec { 'install composer php dependency management':
        command => 'curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin && mv /usr/bin/composer.phar /usr/bin/composer',
        creates => '/usr/bin/composer',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        require => [Package['php5-cli'], Package['curl'], Php::Ini[php]],
    }

    exec { 'composer self update':
        command => 'composer self-update',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        require => [Package['php5-cli'], Package['curl'], Php::Ini[php], Exec['install composer php dependency management']],
    }

    exec { 'composer install':
        command => 'composer install',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        cwd => "/vagrant/project",
        require => [Exec['composer self update'], Php::Ini[php]],
    }
}
 
class { 'composer': } -> file { '/usr/local/bin/phpunit':
   ensure => 'link',
   target => '/vagrant/project/vendor/phpunit/phpunit/phpunit',
}

file {'/var/vagrant_local':
	ensure => directory,
	owner   => 'vagrant',
	group   => 'vagrant',
	mode    => '0644',
} -> file {'/var/vagrant_local/project':
	ensure => directory,
	owner   => 'vagrant',
	group   => 'vagrant',
	mode    => '0644',
} -> file {'/var/vagrant_local/project/app':
	ensure => directory,
	owner   => 'vagrant',
	group   => 'vagrant',
	mode    => '0644',
}

file {'/var/vagrant_local/project/app/cache':
	ensure => directory,
	owner   => 'www-data',
	group   => 'www-data',
	mode    => '6777',
	require => File['/var/vagrant_local/project/app'],
} -> file { '/vagrant/project/app/cache':
   ensure => 'link',
   target => '/var/vagrant_local/project/app/cache',
} -> exec { 'Set ACL for /var/vagrant_local/project/app':
	command => '/usr/bin/setfacl -d -R -m group:www-data:rwx,u::rwx,g::rwx,o::r /var/vagrant_local/project/app',
	require => Mount['root filesystem'],
}

file {'/var/vagrant_local/project/app/logs':
	ensure => directory,
	owner   => 'www-data',
	group   => 'www-data',
	mode    => '6777',
	require => File['/var/vagrant_local/project/app'],
} -> file { '/vagrant/project/app/logs':
   ensure => 'link',
   target => '/var/vagrant_local/project/app/logs',
} -> exec { 'Set ACL for /vagrant/project/app/logs':
	command => '/usr/bin/setfacl -d -R -m group:www-data:rwx,u::rwx,g::rwx,o::r /var/vagrant_local/project/app/logs',
	require => Mount['root filesystem'],
}

mount { 'root filesystem':
	name => '/',
	ensure => 'mounted',
	atboot => 'true',
	device => 'LABEL=cloudimg-rootfs',
	fstype => 'ext4',
	options => 'defaults,acl',
	require => Package['acl'],
}

user { 'vagrant':
	ensure => present,
	groups => ['vagrant', 'www-data'],
}


