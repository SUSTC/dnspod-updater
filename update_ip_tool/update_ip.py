#!/usr/bin/python
# -*- coding: utf-8 -*-

"""
# Copyright (C) SUSTC-IT tengattack
# License: MIT
# Version: 0.8.1
"""

import os
import sys
import re
import socket
import urllib
import urllib2
import json

__TIMEOUT__ = 10
__API_URL__ = 'http://api.your.website/domain/ip'

def check_ip(ip):
  regexIP = re.compile('^(([01]?\d\d?|2[0-4]\d|25[0-5])\.){3}([01]?\d\d?|2[0-4]\d|25[0-5])$')
  Checking = regexIP.match(ip)
  return (Checking != None)

#get local ip
local_ip = ''
if (len(sys.argv) > 1):
  local_ip = sys.argv[1]
  if not check_ip(local_ip):
    print 'Bad ip address.'
    os._exit(1)
else:
  local_ip = socket.gethostbyname(socket.gethostname())
  print "local ip: %s" % local_ip

local_ip_cache = ''

try:
  fp = open('local_ip.cache')
  if (fp):
    local_ip_cache = fp.read()
    fp.close()
except Exception:
  pass

if (local_ip_cache != local_ip):
  print 'IP changed, updating...'

  #load config file
  try:
    fp = open('domain.json')
    domain_json = fp.read()
    fp.close()
    domain_conf = json.loads(domain_json)
  except Exception:
    print 'Error config file \'domain.json\''
    os._exit(1)

  result = False
  content = ''
  reqURL = __API_URL__
  data = { \
    'domain_id': domain_conf['domain_id'], \
    'record_id': domain_conf['record_id'], \
    'sub_domain': domain_conf['sub_domain'], \
    'value': local_ip
  }
  params = urllib.urlencode(data)

  try:
    f = urllib2.urlopen(reqURL, params, timeout = __TIMEOUT__)
    content = f.read()
  except Exception:
    pass

  try:
    d = json.loads(content)
    if d['err']['code'] != 0:
      print 'Error(%d): %a' % (d['err']['code'], d['err']['message'])
    result = (d['err']['code'] == 0)
  except Exception:
    pass

  if (result):
    try:
      fp = open('local_ip.cache', 'w')
      if (fp):
        fp.write(local_ip)
        fp.close()
      print 'Update sucessed!'
    except Exception:
      print 'Write IP cache failed!'
  else:
    print 'Update failed!'

else:
  print 'IP not changed.'
