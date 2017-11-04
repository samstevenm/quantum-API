# An API for Lutron Integration Commands

The Quantum API sends commands and returns information based on the area and light level specified.

#### Files:
1. A simple `PHP` script to send Lutron Integration commands to Processor or NWK over Telnet
2. A `ini` file containing *AREA NAMES* and *integration IDs*
   1. *To-do*: Create a tool that automatically generates this file from the integration report.
   2. *To-do*: Use `JSON` instead of `ini`.
  

#### Requirements: 

1. A Quantum installation with Integration IDs
2. A networked Quantum Processor on NWK
3. A networked Webserver with a public IP that hosts the `index.php` and `lutron.ini` files
  - *NOTE: This Webserver can be the Quantum Vue webserver.  This is probably the simplest method.*

The *LIGHT LEVEL* must be a value between 0 and 100.  The *AREA* and it's *integration ID* must be specified in the `lutron.ini` file.  The command will return `JSON` ouput.

#### Installation: 

1. Modify the `lutron.ini` file to include:
   1. Your Processor or NWK local IP address
   2. Your Processor or NWK login information
   3. Your project's list of *AREAS* and *integration IDs* . 
      - Any spaces in the *AREA* name should be replaced with UNDERSCORES
      - The *AREA* name goes in the first set of SQUARE BRACKETS (underscored if applicable)
      - The *itegration ID* goes after the `code =`
   
   This should probably be accomplished programmatically (*see to-dos*).
2. Modify the `index.php` file if you:
   1. Decided to place the `lutron.ini` file somewhere else, or rename it.
   2. Want to show less information; the `proc` and `config` JSON objects are useful for debugging, but should probably not 
   be exposed in production.  These lines are marked with `//recommend hiding after debuging`.
3. Place the `index.php` file and `lutron.ini` in the same directory on your networked server.

#### Usage: 

`http://yourserver.com/path/to/files/?area=AREANAME&level=LEVEL`

Example: `http://yourserver.com/path/to/files/?area=entry&level=50`

This command will set the lights in ENTRY to 50% and return the following output:

```
{
    "config": {
        "telnetPort": "23",
        "localIP": "192.168.1.111",
        "userName": "eng\r\n"
    },
    "area": {
        "name": "entry",
        "exists": true,
        "id": "17"
    },
    "level": "50",
    "proc": {
        "commandWrite": "#AREA,17,1,50,3\r\n",
        "commandRead": "?AREA,17,6\r\n",
        "cleartext": "login: password: \r\nQNET> \u0000QNET> ~AREA,17,6,3\r\nQNET> "
    }
}
```
