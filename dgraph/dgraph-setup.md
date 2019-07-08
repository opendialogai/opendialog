# Dgraph Setup

## Introduction

OpenDialog uses Dgraph as the backend store for conversations. These instructions
assume that 
- You are using Vagrant Homestead for Laravel Development
- You have Docker Desktop installed and functioning

### Step 1

Within the dgraph folder run `docker-compose up -d`.

You should see output similar to this:

```
   Starting dgraph_ratel_1  ... done
   Starting dgraph_zero_1   ... done
   Starting dgraph_server_1 ... done
```

### Step 2

To test that Dgraph is effectively up and running visit `http://localhost:9001/` in 
you browser. You should be able to see the Dgraph explorer interface.

### Step 3

Start Vagrant Homestead as you usually would. 

### Step 4

To ensure that OpenDialog-Core can communicate with Dgraph run the following test:

`phpunit --filter=testDGraphMutation src/Graph/tests/DGraph/DGraphTest.php`

Then visit `http://localhost:9001/`, click on "Schema" - you should see predicates such
as `ei_type`, `causes_action`, etc. 

Vagrant Homestead connects to Dgraph on `http://10.0.2.2:8080` - so if you do see the 
expected results check what IP your own Vagrant Homestead installation has set the guest 
machine gateway IP as. You can use

`netstat -rn | grep "^0.0.0.0 " | cut -d " " -f10` from within Vagrant to find this out.

### Resetting data

To drop all data, you may run the following command from within Vagrant:

`curl -X POST http://10.0.2.2:8080/alter -d '{"drop_all": true}'`
