This code tracks a DSpace repository via OAI-PMH and mints DOIs for items if requested.

It currently works with the DSpace 5 REST API and the DataCite MDS API. The generated DataCite XML is compliant with DataCite Schema version 4.1.

The code relies on a local MySQL DB, the structure is in "doiMinter.sql"

To run the code there are three tasks located in the tasks folder:
  1. harvest.php
  2. registerDOIs.php
  3. updateMetadata.php
  
A poster describing this work was presented at OR2019 and is available at: http://hdl.handle.net/10754/653099
