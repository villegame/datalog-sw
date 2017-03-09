import psycopg2
class Database:

  def connect(self):
    conn = psycopg2.connect("dbname='temp_mon' user='temp_mon_user' host='localhost' password='password'")
    return conn

  def test_conn(self):
    result = True
    try:
      conn = self.connect()
      conn.close()
    except:
      result = False

    return result

  def get_latest_values(self):
   
    # Open connection and cursor for reading database
    conn = self.connect()
    cur = conn.cursor()

    # Get latest time data was logged in
    maxtime = 0

    cur.execute("SELECT MAX(values_time) FROM temp_mon_schema.values;")
    for value in cur:
      maxtime = value[0]

    # Get devices to display by set screen and screen order
    # Array of devices, each element will be an array [id,name,screen,order,value]
    devices = []  
    cur.execute("SELECT devices_id, devices_name, devices_screen, devices_screen_order FROM temp_mon_schema.devices WHERE devices_screen > 0 AND devices_screen_order > 0 AND devices_screen_order < 3 ORDER BY devices_screen, devices_screen_order;")
    for data in cur:
      device = [data[0], data[1], data[2], data[3], 0.0]
      devices.append(device)

    i = 0
    for device in devices:
      query = "SELECT values_value FROM temp_mon_schema.values WHERE devices_id = {0} AND values_time = {1};" . format(device[0], maxtime)
      cur.execute(query)
      for data in cur:
        devices[i][4] = data[0]
      i = i+1

    cur.close()
    conn.close()

    return devices

  def get_max_screen(self):
    # The max value of screen numbers used

    conn = self.connect()
    cur = conn.cursor()

    last_screen = 0
    cur.execute("SELECT MAX(devices_screen) FROM temp_mon_schema.devices WHERE devices_screen > 0 AND devices_screen_order > 0 AND devices_screen_order < 3;");
    for data in cur:
      last_screen = int(data[0])

    cur.close()
    conn.close()

    return last_screen

  def get_devices_by_sensor(self, sensor):
    # Get list of devices by sensor

    devices = []

    conn = self.connect()
    cur = conn.cursor()

    query = "SELECT devices_id, devices_source, devices_type, devices_sensor FROM temp_mon_schema.devices WHERE devices_enabled=1 AND devices_sensor={0} ORDER BY devices_source;" . format(sensor)
    cur.execute(query)
    for value in cur:
      devices.append(value)

    cur.close()
    conn.close()

    return devices

  def insert_device_value(self, device_id, value, time):
    # Insert value of a device

    conn = self.connect()
    cur = conn.cursor()

    query = "INSERT INTO temp_mon_schema.values (devices_id, values_value, values_time) VALUES({0}, {1}, {2});" . format(device_id, value, time)
    cur.execute(query)

    print query

    # Commit changes
    conn.commit()

    cur.close()
    conn.close()


