import asyncio,json,re
from pathlib import Path
from email import policy
from email.parser import BytesParser
capture=Path('scratch/auth-smtp-capture.jsonl');capture.write_text('')
async def serve(reader,writer):
 writer.write(b'220 localhost test SMTP\r\n');await writer.drain();data=False;lines=[]
 while True:
  line=await reader.readline()
  if not line:break
  if data:
   if line==b'.\r\n':
    raw=b''.join(lines);message=BytesParser(policy=policy.default).parsebytes(raw);contents='\n'.join(part.get_content() for part in message.walk() if part.get_content_type() in ['text/plain','text/html']);tokens=re.findall(r'token=([a-f0-9]{64})',contents)
    with capture.open('a') as f:f.write(json.dumps({'to':str(message['To']),'tokens':tokens,'subject':str(message['Subject'])})+'\n')
    writer.write(b'250 Accepted for local test\r\n');data=False;lines=[]
   else:lines.append(line)
  elif line.upper().startswith(b'EHLO'):writer.write(b'250-localhost\r\n250 SIZE 1000000\r\n')
  elif line.upper().startswith(b'DATA'):writer.write(b'354 End with dot\r\n');data=True
  elif line.upper().startswith(b'QUIT'):writer.write(b'221 Bye\r\n');await writer.drain();break
  else:writer.write(b'250 OK\r\n')
  await writer.drain()
 writer.close()
async def main():
 server=await asyncio.start_server(serve,'127.0.0.1',1025)
 async with server:await server.serve_forever()
asyncio.run(main())
