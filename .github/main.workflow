workflow "Test on PHP 5.6" {
  on = "push"
  resolves = "test"
}

action "install" {
  uses = "MilesChou/composer-action/5.6/install@master"
}

action "test" {
  needs = ["install"]
  uses = "docker://php:5.6-alpine"
  args = "vendor/bin/codecept run"
}
