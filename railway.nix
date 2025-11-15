{ pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/nixos-23.11.tar.gz") {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php82
    pkgs.php82Extensions.gd
    pkgs.php82Extensions.zip
    pkgs.composer
  ];
}
