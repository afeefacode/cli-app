Development
===========

Build the docs
--------------

PHP API Documentation
^^^^^^^^^^^^^^^^^^^^^

The API documentation will be served statically on GitHub and hence needs to be checked into the repository for now. There is no github action yet.

* Install ``phpDocumentor`` globally: https://docs.phpdoc.org/3.0/guide/getting-started/installing.html and name it ``phpdoc``

.. code-block:: bash

  rm -rf docs/api
  phpdoc --cache-folder=docs/build/api -d src -t docs/api
  firefox docs/api/index.html &

Sphinx documentation
^^^^^^^^^^^^^^^^^^^^

The Sphinx documentation will be built by Read the Docs automatically. To test the docs, you may run these commands:

.. code-block:: bash

  cd docs
  rm -rf build
  make html
  firefox build/html/index.html &
