<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#07502a">
<title><?= isset($judul_halaman) ? e($judul_halaman) . ' - ' : '' ?>Website Ulangan Online</title>
<link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAAA7CAYAAADLjIzcAAAd50lEQVR42t2beWCVxdX/vzPzbPdmDyQkJBA2F3CjgiJrRMCl9m2tFtu+aqm1pbXuCuJr6y9NX60o0qpUq6iIW2uJWrVi2VxCEkAURYEgEIGEkD25yV2fZWbO748IRRaNXd/3ff67985y5vPMnHPmnHOBf+PzcNVzObetXtTv3ykD+1dPSETi/pqnz+xOJC6M+vEBeyPNma50m2zhvHxaaPz68ksv9f9PAnh688q0pu76C1OpxFelUqlEQK9X1b6/y1U9LD+v/2gh7G8I8DAT/JUBPP/Fp64s7/4/AeC5j17L2dfRdHHcT01Tvl/X3tX5yp+2rWVJxf7DNs1R4FqRlJscbf7lxMHF6coyL+cMJxjcWN5PhJ6ruGpR+/9KAK/v3JBZ21T7rXgqcYGXSm1q7Ox4Zdn2ymLbMC4V3Aibir9mCL7K54YV6MR/cGIzlKL9QqJiWEFh1EozLjcM6wxBfHm2Ci99+bqHOv9XAHiPyHyr6olLom7qEuXJ9/fs+eRPL+/eeIJjWpeCGz0C5vNTx2TXVFxaoQ7tV1I2y3Hd+mm+ppkEokAFzw7vV9AaSrNnccMYLWyzYpQ2n13848XJ/7EAfl39zORoIvKThJtqao10P/PHD9cMdCxnlkGs3ST2dMuCyvf6IlPB3LOnuEJfBgUTUi8dVJAXcxz7x4wbgxxm/3bttU+8/j8KwO83vTxwd1fTDUk3ObC7K/bokndXkhEWP7RIeEzjic6Fa9/9W8bt/19TJkPiqgDSNQOxeFDJoHQGPcfg6LS4eXf1dc/s/LcCYIxh/tuLv92TSHwnkUr96bkNf1nvkvcjIaxiaPZYz4K1bx2rb1lZGS8vL9djZs82Ny1eHBzTbAIsb27phZLhe0zJWssLnsobWngu5/wKh1nPq1DWo5t+fOz+/zQAv9/+8sC6/c3zYskk2/HJrodW1W8+y7DZxULzV/LcjOfqFq3wjtbvxGum9esM+9cpzjOYyxeHbJaVJO9k5WO3ZSC3fWHNS7nXlRaHDa+z8TcbUgf6FZZ9LSzjPT9Qgk0n4n/Iz7A3W+HQ7ZzzdCZCd2y+4bnav2Ud4m/ptGD90guamvaXxZKx1x9Z+fzyerdrrmEYuRbR7ZEF69Z2baxTANB/7uTTQ6XDrgiPH9qRWre3EwBypo0qTPLgMs1ZxOLWJwg5W6UOrtammJzph5/S44ryENbLBDf2xmvq6wrLvhbOPj3XaJr/Ziq5vmFj5lnHv6OFujwWeONT3d58K81KkvL/u+jckbJ19ccf/lMBvL7zdXvUxVPmdnVHpnW0N5U9vmHNIDOUfpPJ2O8jC6oeSK3bFzvQdti8mVlJxO7TkJoTMHTG0D3++JLvS+knhGIRaSKXwEa237XyL3bpsK/YJN5p0rGNpsN/yzT3faIGf13DZmNcwe0e1xc7ZwxPeO/U702s392dqql/LTxxSFgJdlt3LPlutmE+7gl1/YDzT5w06oLx6+pXfuj+wwE8+c6ygm2t+xa2xzqTf1r76q/fad93nRDG0CBlz43e/9Z7ADDwtmn9+KTia5xxJc0tC/7SkjahxOSap0tGXw08Y28gqD4cGI2+UOMkVBonvObW1DdknzFkgFB6k8l4KRNkCcbJMkVKnDWiXXN1qxGw123h7A+dPjDNPL0ww9u0v9utqd/mnD2sBppdFU+5J7J44g5y7NMS8K4pPu8r77au2tb5D9MBD2x4ZlRLV/svU4nUssWVr+wwwuKXgrEVkXvWPdKrp3qf3LLSYhnIpaRZ1ND0mHBZmzL1lQF4YGnrvq7fvLn/0InpsHn6/WBCRueSdbGSOaUFHMbJEaYmEKemcMD2hcy8DRHder+QeqnwArf14XfeOaBMF8XfuFZzdjaTrCw/N3yqNNi1Bme375j3+lt/N4Bf1zx9TmtPy83RnsSvlr67or9pmD/kgi/ovntt1ZGqvdRI99VSBdK2ZgkzQY/AMVrDaeiuL690v+z5LLx++uDZORMby8vLdd7cKfOgWNIy7bXKD4ok43VmPNTYvPi1JABk3TLxbOLsVtK0KD/d6gksayEn/ru9P//L038zgHveevyiSDQ6K9LR/rNnt751jmkYk6HVrd0L19cDwKiyUVZTNPvS0zLN5yvLKyUAZM6bfI/J2Aud89e+i5kzBSo+6/EdeIpvmhkSmb6dDSAb2W5l+VPHBFRaVurURukHyGRPt5dXxnPmlU5SpK/MDDL/X6YX76l9uDLeC2HyUMHVfCLxZpYTfltZ+lGTrJd3ly2//0sDmL/2iW91RSPfadq9d+7Le9/7nsWtAnjuvK5FG6MAMOqnpemdIQxJmv4dxER5QTz9k7pFK7ySn5YWIO/INz74tgtzUuSd5CgaJlWQrTg3LWEwAPBdn4QwAw7R7ZHcE3ZCWxvmL48cTa6s26aO0TK4VZC6VXDzNKk07/l19ct/Vb7Ts9rhLWCgvcUp59lYLp6UTL/RUvbmr6ivAO5+8/FLuuKd/7m/oe3m13bXXGcZBvJDhbfXllccvKsPu3764K6Qf48UOA6ktzLJ43YP3dWxuKr50LEGXTN9oBtS0w0tionJbsm5qU3K1GCDJHQ6Iw6DeFJwVg9P9xgg3yTK1oQmbog1+xasafqMd3jT9FIp3Ej3fdUfZd426XFiusnxRE17RtVqlEMDAK4bYec4BfM1IZVhWQ8FBpYyzle0/GLNwi8E8KvKR8/t6o5d27S/8cbX6t79iWky2bm78I5Dt3LOrWecJAynS3myX2Aa99uK7vZcfBTPre48IMTMmTPFhoEdF0pGJyvO2wKHjwpMPT3gOI4J5hD/dHLqFYNpBlLkctK7bMnXOAldC8h8n3TtyZvFa5WVvUfsoMKcO/kngWATBWeV4RS27v9N5YbPHJvSUmPLOPYraKVDPO23nqWW2lIv2z//jcXHNIP3VS0d09bd+bO27pabX63dcLltGua1oXNur3z4YX1oO2fS0F8qxm4kje1KuhVcMiP64LrtqOxdzrB507P22NGrDQjHCxnZ0TR9s3T4uVqwAYxgEAHQANO9AIgAAgEcBgkxQFoYH5hqDOPmlrDPqaOAT86ZWvRxT3XDgWPFMseVcCdl/cHOMLd7MDLD40pKB559QmtXdV0SAOrr6/Wt02e9sSHYPcOX3ikZXN7pCf7LnNITm2Nr63YdAeB365YV7Yk03RfviP1i2UeV00zTKi4IDZj3QvnD8hDHnPVPTbmAK7Qo4kMkY8NFwBqiD65ffaDJwGum9XMD72rOeXs0g12USsOPIHgmlAZRn2JmvVAMnqlMXuoKLSzi7wYBn8EmlNT66+uTAJDY0LDfmjxkhsuCezT5Y0ior3Pia6I1u9sODFVZWUnDZoyrTAaJS1yNXC6MR6SW92ROOGFDouaT9oMAlq1bFtrS0/BAIhZb9tT7K9IdyzlPkLyx4VcrU4fKNqZ5jBkJ2eVS8G9CoJsRurP2qd9Faxtl752+1Amk+ikx3ZTI4j8NwuZUrTSEZiDGQEoDjIGxv549OuQcssNuQQEAbrHjFNNFYW2utjQ7O+O8k96PV+4MACB1bsknhmLvM43/NBQWtS54e83hPNsra1V4UvFaDX41U3I/GF9PXP88Z9Lw12PVe1Ji5rKZoluG5iYTqdiTb79aFQo7cznopq571x0Rimre1KzzRwx5zXN4D1N+rqn1/a1PbGg5eDROH3IFB08l0tmsIGxNpIDAGINigAUft5yl0drjosMVAO/dfFoBghGIs09haAAcUjPk2SlMG0SoSxpFPul8y9U1WnqjEuvrN/e+4nqdf9bwAq2BroVVDw+YVzokXDpkSLKqvuVQuVPr9vnhs0o2gvM7jMB+QUFmy5h7jvte4yoxeNK0jIZE423N7c0LO7U3x2BsSeSemk2HL37ALaemOROOu5llC8PnFIf2/5zZ6TT2fFgvASB/zjmncs1OSTn6JD/NmumoAJwDmjgCzSG0j4kDEvjmSIWYa2Bnl4bJNaYU+2hPCPgBQNDgjEORRr7p4aHzEmCksGqPDdhsMAO1mgqprCknJGI1n7QCwIAxJ3STIQ0xZcjtitMVTNPOVE3DjsPld9c3RO0pI9qYgZ9kNLtL/NbOWUXDcp7nGdUtiZZoW2enG8nnRIqDH9WHlkbWON9UI2IUfEdB/ciHeXL9U5/a+mUzhdA4Xxss5jrWFUwqlJUmcePYGLj2MLEwinum+Tg+n+PVOgt7uxUWnZvC9OIUfjLaRa7t4eT+Ac4dHIBpBZsR5p8dw8Wj4tjXo5BhSEwo9OA7uAzcTFBKTsfMmQIA6hat8DyiHkvgeaWwg0nm582ZOvxoa7AZtYGpqB9L5lHES9atqPN5RUWFUlp3eMwfLDj7mBgrOSoAj/ZCm1KQuVlI0Wxq3nrQq6tuG8klBSmbzoOF9EADO9s0XB84p0Thp2dovLOPcMOqNCzbGUaXMuBpIGRyZIUYJg708e1RAfZHgYA4RuW4+OqIAJsbBFwlMK7Qw8wTfUiOcMym88EYFRdHRh2Y/9r0qZugMAKcjldCnBfWpnm0NXAtR3Mu6rkbDIHUewGQAQBQbAvj1nHEvA0AHwvghcM799xfvZsBVw+/9fziHlOVtN+3euNBZ8K0xhBYIhDyHFIEzoFXP8mCqxWkYqjabyLqc1gGh20A3a6FuWsERvVzsT9mIivs471mhg87TTDO4QhCUnL8fpuBdNvE8WYKp+crpPEwEkKfrU32ugAfA2ALAFSg1iDJtqSZ9GLLvW+1R45hYBTYMDJQ63n+ZEi1FQA4AIQsawsjGulotGhNwz4nPIW6e1c0tt+9uuagZXh0tulDFfimKiKHZTFNMBlDu0eI+wLZIUKGIAzJkiANeIqhdKCLikt6MK0wATBg8YcZeKUuDMY4SCl83GXgxV0ZqG5ykBFiCAvg1P4eRvdPIOBGZmCiiGudO/vR2SYA1JZX+O33vV3Tcnf1sXMIZWVcMRQ6gd4LhsE6UDsPAuAJox6MZzpGKAqAF95c2r+vN7Z4U0ueAKTL6CStCSNzFPpbGgQBScAp/SSWfiOOWaemEGI+0rhC2FI4f3AS00s8SKkQaCDN9HDdaTE8PCOKMwf6uLPawu4eByZJJANC2NK46UwXGVYAl2OUYoxV72rsc14xC5sztUJ/0eb6TOmsLN/efhDAxTi1hQguN3l/JpXnGXpYXweOuMl+XHDPN6kgz5KYPMhFl68B0uBMo7rRwIINIbR2aaz8TgSPnRfB1iaJp7elY1/CgmaEEAvw4LQY7psWxdVndqH8bB8BcXgKsIRCUgL1EQuTiwOcMcBDKlBDJDHqEJTXVznNoGsYM6iJetxcJXWq4eOG1oMAysvLJfNVnc/UCeBsM4c4pa8DZ6S45SghlWLhM4sCZDtA3OM4MUfh2q8oBJph1V4LHYGF7pTAecM8fH804bHNNnbHbMiA8NXhATIdge++nI4Xa7Pwp1oLngbAGDItQqbFULHdRtRnOLPQBzjP5GBceF52n4MLko8RBvuEou5orvS2Xmf8UwAAQMJYp0CTSNFWV7DRfQ9bBBAaBEZsSGYAR2hAA8PTUzhvWK+VNAyGF3ak4+svZeAP2xy83SDwQbONfukCWklkhw3MWe3gxU8ycdmfc3Dvu2nQJGBwQoal0JQ0sWRbCI0xE4PTJRhXDBwgQX2OapPgJ5qwt/leMFp6avNBy3BQkyu+mUldkm2FmixN/XJmj8nqy8CuxYKUBUNppALJcM4gF0Mzkxg7UIKThEkSWUaAiQUJTBzoQXETW7psMAHk2B4SkvDKLgN7UzYsUwCcg3EDBAZLaFjCwLpmEzsiNvZFAdNgYErHQZoUM6J9kfGEuV/PgFIlostt0UoPMVy8cwSA3OZBexg3fMcK5Wotkyo37fhjRGfSD/2sRbjbl4EjFGvf1GJjRK5CzawITs330R7z8d+Tu7Hk/AiWfiOFa8/UeGEbR3vKAEgibDLEfIE2T0AwDkBDfWpvuFIYnC7hao5OzwQxICEJHR4HlNEI6VNuitoOleW0stKjHolukRpFAu3G/mSuDLxUx/b9u48AsGnx4iBg9EE3d8cyEu8wUlMOHWTEdefbeXOmDv/QlXdkzZkwunD2mDAApA2UrQG0kc7U9vc7DDzxQRj7E2H8ojoDKxtC+NoooD1l46IKB1ctT0fV/hCmFvu4fKSPkJDwda87ojVBK44MDpSEU1gwNYrHL+iGKQAvECh2fJyWz7Ch0QRj2MoMRur4kZ0Hwmt58yaetieV+nnG3HHHjbjufPuz9l+P44bzQSrRM0FI/QEAdQQAAICv3oSSZxsB3iXQKSgrNQ7emxNRy+Pyai/TudU38F9kqzQAqLthhcc4a7ck28MClrxzYzrOW5aJrZEQXtiVjrOW5ODWN22kWQzZtsZ3R/bgwXPjsA0JxgDXJ0BpZNoaYZbCzBMSeOWSCL5/cgzCMPFirUBI+LhrSgwxj2HtHjMRImqQJHvqbljkAUA40Zbmw5gbZKfdwjT/KdBlH5qCU9BnOAqbKdATkPArP+MdfubCo5xNRDqHZzgBEXf6J2nogd92LFkXSwvMGjMhn7KVUdmyaPNfnQ6lPiApLSNAlS8E4tIC5wy+4ihIIzx6oYfbJyucnE8YkGXj7hoLT34YhqsFjs/VKEp3UT4+guWX9mDuuCjyMxjmvJmGsU9m4r0m4NHzk5g8VOHnNenoTooNliJTSXYwC7RzcWUHFCpFTD1pg1fVfRq3BIDHvOoRjDQL7Y372tX9/Jj/zjEBbJm/PKIZdjJDnc65eM8HTTv095yoURn9VeX3h/dYz6Gs7GDfjnhiK9cszVbsNSPQAeMHs6fgHHh2q4PLXjawut7AQ++HULErA9qw0Bg1cPXYFGqu6MHEIoksJ8DjH6ThrKf6Yem2dIwp0HhpZhKn5gW48tVMrG+0ZLZif2YknZKTUh8d+paLlP1i/O61P8iIhd74rI2SEwVnHyVbOsfJINgSbYx2fW5MMP/2Gd8MuLxEeeJ+zfxr4qGpV6G8XH+Rps2/qfQsk9i4nnQ+LkgT30XQG++SBJBiMAQDYwRGDMQA0oQhaS7mTUqAE3BXtY0u30Ii4CjI8HHlSRJTSgJU7jXw2IcWmmUa0lPBslBcrzMYNjY9sHb9F6r/sjLe3127xBb2I7Jm1xw3kvh9z0ctLx3axDi8j6Ott32urs9iIhUBiXT/jRPjwBdmXtt+U7kh7/rJY52YXqM4jdSOMRpSQTAOZhAYEQgEYr1hD8aB3QkHV68wQQA06xVlSKbE+CID77b0OkvNSRPcMZDuB1uy4rRCAqGmB9Zu6Iv5G9SzYWTC4sLYG3U9TxarZPyIZA4//IuG+csjAtjiiuBscKrmmr7eZ2fLtZ/h4IWZMTzIXbkLhgD7TAKMfWbTCQ5ACDDR6/QITmhJEP74sYHX94bQ6tswQwZML6hzkvQbCV1sBfoPR8mqHfWJWcmvc7C1Xnv3OcqTm+J18fYvBAAAzNMv+kQXWNqpIibG5B1m+495L1i8psfU9JQgGpwZx3wj6b/DRW/8W7NjZ2YOjQlKCBiMwTYEDJPBSAUbQ3F1F/PUYOHwJxt+Vx3piyyjykrTBccZWRDryPW+KgK17KgxgqN92RGmGqaJmUwM0IS9vitn9HUX7F9U2WjAXqxJDwjF8bgRVw9D6Rg3+MG4HwhgdEgJCB14pwQwBm0KkFYxO6YeCcXUY1zSQEX6sf33Vjb2VY4WV8wAN/f4tS1FypXUoVvW9xkAyiulAF4NuPy2pfTLROwilB3ZlgBW+qmv0P+WSWOybigdAgAN969s5tx6kADPSgV77Fhwm0gET5u+3ifAAIMDBgcTHMzgILP3MwcDC6jRSARPm3F1G3f9OqYRCDP0QOdD65r6uviyMnAifTEX9kup7vh3/VTqBdTiqBWoxrEGyUnJlzptdTmk2c1NqXK8aeMieOMzFAtvmzbWS8nR4TkT6j2ufsAVbj/wW+vC1QkAz/SbO/UEQ8oZ4Zj+iLja6IZFuuKsmEiXEGNpYIDSFBeM1Yd83agUJQ1JluQcATOWRx6s/PhgWuz6KccFFr5nwX6x/b7Vm48l+8OpqWeBkx+ubU8mfT1S+2LusdoeE0Ddour2nJtL39SG+o5g7A9aBbMAfAZAKuAdZNBEwcVYwwzNjdy7ah8AFM4eE06z81TdohVe54K3dgDYMeSWqSVJRaO5SwMdrT4mk9UFIIMDsCQpzXQgiRExNJkOvd9xz9qGI0JajrA4giku887MmjtpV5ZmCxsWVu05bFuyYJ6exU37+Xhn5HvSc1fGdzZ3fGkAAGCZzpOuSj3jSPMZF15Gzs2TJ0R+XbXuYJxw4eo9w+ZNv0G1Bl79U6vc3hT1pDExU97Vw7orADxxoO3ehW/VA6g/kEBxYWWSlCHlAo7D3MBNRls/LYrqOcTBeSRVNZa81PC2bKsiginbs1NrdhJJzhRWpfiRxzJv3tTxHqNQ+sedLalkMF4kvG/9XQUSOTdOvlMKMJuLVS5TV8X3FFx5tJz/zGUzxV82NcwmmN/jDPsEsee676l6BWVlfIhcO2jvXW/Vf9kCiaIbJ30rbtPNIP1Yz73rnwSA3FunXMwVz+lY+PYTRyvQyE2xJaawnqS1uy9S3Ynuzm3NZZ83h/FFQoQVfyROqsKA8SyzZSJraNM3eoCXDm+3cVubaQZ8t2DsTs/Af2nD2AoAhW5NcRTuz9Jvn7Dc9FV+Ydrgp2rLK3yUlRoD4lZu68LVbb2JlxlpqZB3odbqBFubuzvtt/+Q6vJXEdkNxPDDwtlj/ti8eFNyaHfiz3FpO0fb0/kJdqHHEM/e3BSLef5ZLBZc9EXr430xa0LTH12Kz7FI30+az8q4ZtoRwcj68ko38uv1KzsWVi23XbbMiNrtAOAZwbjANGcwTVdyzatqyysCAMgOggtd030p64beO7xt80yh6ApGSEpS541pni26Fm2MRu+t2miSqA+yssb0Xts3BTuWrIsdPv/A26b18zj9wA47j/TEEnPI957raOho/rsBAEBBi/E4FB/CUmwYh1jF0/2bPq995wNrH+xatCIKAMynrzCf5jPwts4Mqw4AjZk929RkfU0DSR2iKQDQcNfKFk2iS3DzZGJgu9PfLzg4f2jAgrTwoM8ttw10cCPj7A2zqu54SgV5Zjt/vC9r6xOA2orKuK3oLqn1z7jiL2uiEf3nTvjq5+UPDh6hpPXb44aPXGJJvm9gmy4EgN3p289gQIFJxi6QvgBlZRwMxIk2pLnmM47kTznaGXxw/vIKv/5zaohy5kw6XzI2rP+e2HIv4c9lbnBnc3Nzn6rK+1wnmHivcU94bMmJSgZTzbC5QGr5M2f8wJbMc0e0Jyvrj/k3l+i7u2PNr23Sc2dcWR2qT8Vra2spffzgEOngz90Lqn6fNW6we2JX7b7mTc26ZMJpO6yunqaGh6o/jm9o2PdFMp16y4w0b0LReOL6JmY55cHmxptlwt8e2dr6eF/X9aVqhUdcdn5mVyjyEjPYEso2PgGpqxSJTM15m8OMHZLkHkOIHcpItE+rLUlUHKNC7Ms+BLCCW2aEfYflcu0frzkN44pGElGR5CwqDHtp+O2GkxM9sW/zRveirq6u6D8FAAAMuHz8yUGQXGz2uGtCTnq1X5DW7ufxTJAYrrgcrhgGME9ncMUS3NItBpkRLnU3MdZFEJ3C4d0+dJJJrbjUyjM4ESQ3GTOEJSwdl1nERI4G5XFiudrSGVJTkeGrLHLMFGnRzgy+O0TYyVsSUaPJy3Ujsaky5U3VMf/KrrqWL1U0/TdVi/c/Y+jxjOvLBNhXApPnWIbRprjcaRnOTmSFdyPbTFK6JWSuFVZS5ZMM+jHDyCENh0ibGpTJwELQMDSIOEBKsEAwFuWakkTMA6kohxFlttiPmIzbETdgnYFFsVSJl0icYDCcqHxZqBSPQ9L75Mlnu3a1bP+XlcsfUKJFI4oGJoU+iYfEKcwUxxFoiLCYwZmRIoYoGKKMiU7bMjq4QJvm6LRyshPhnDTt+VqnUimkO+lMSM072lodIWUuC1i+9P1+Uun+WukcDp7FtQ4rFShoatCB2sUCtcXS1paJnzQ1VRwS5f1XAzjSGQP4k6cMzkpQMEAyVQywYoObeZbgBQHp/jBFDjNFtuLMMRjjjABFRJCKKAgikDpicqtDyqBFgdrIU60Gd/ZleGL/2D1jOyrwj9Er/zQAfQpWAFZLbq5TVFQkACCRSOhYLKY62tvj9C+W5f8DgcN0OKDRpaMAAAAASUVORK5CYII=">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    :root {
        --bs-body-font-family: 'Public Sans', system-ui, sans-serif;
        --font-judul: 'Bricolage Grotesque', 'Public Sans', system-ui, sans-serif;

        --ink: #1d2733;
        --ink-soft: #5b6673;
        --paper: #edf1ee;
        --paper-line: #e0e6e1;
        --surface: #ffffff;
        --garis: #d5dbe1;

        --brand-primary: #0b6e3a;
        --brand-primary-dark: #07502a;
        --brand-primary-tint: #e1f0e6;
        --brand-success: #2d8a4e;
        --brand-success-dark: #1f7a43;
        --brand-success-tint: #e1f0e6;
        --brand-ochre: #e8c400;
        --brand-danger: #a9423c;

        --bs-primary: #0b6e3a;      --bs-primary-rgb: 11, 110, 58;
        --bs-success: #2d8a4e;      --bs-success-rgb: 45, 138, 78;
        --bs-info: #2b7a8c;         --bs-info-rgb: 43, 122, 140;
        --bs-warning: #e3b800;      --bs-warning-rgb: 227, 184, 0;
        --bs-danger: #a9423c;       --bs-danger-rgb: 169, 66, 60;
        --bs-link-color: #0b6e3a;   --bs-link-color-rgb: 11, 110, 58;
        --bs-link-hover-color: #07502a;
        --bs-body-color: #1d2733;

        --shadow-soft: 0 1px 2px rgba(29, 39, 51, .06);
        --shadow-soft-lg: 0 8px 24px rgba(29, 39, 51, .12);
        --radius: 10px;
    }

    html { -webkit-text-size-adjust: 100%; }

    body {
        overflow-x: hidden;
        color: var(--ink);
        background-color: var(--paper);
        background-image: repeating-linear-gradient(to bottom, transparent 0, transparent 31px, var(--paper-line) 31px, var(--paper-line) 32px);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    body > .container, body > .container-fluid { width: 100%; }

    h1, h2, h3, h4, h5, h6 { font-family: var(--font-judul); font-weight: 700; letter-spacing: -.015em; color: var(--ink); }
    h1 { font-weight: 800; letter-spacing: -.025em; }
    a { text-decoration: none; }
    a:hover { text-decoration: underline; text-underline-offset: 3px; }
    .btn:hover, .nav-link:hover, .navbar-brand:hover, .badge:hover { text-decoration: none; }
    .text-muted { color: var(--ink-soft) !important; }

    .navbar { box-shadow: none; border-bottom: 3px solid var(--brand-ochre); }
    .navbar.bg-primary { background: var(--brand-primary-dark) !important; }
    .navbar.bg-success { background: var(--brand-success-dark) !important; }
    .navbar-brand { font-family: var(--font-judul); font-weight: 800; letter-spacing: -.02em; display: flex; align-items: center; gap: .5rem; }
    .navbar-nav .nav-link { font-weight: 600; border-radius: 7px; padding: .5rem .85rem !important; margin: 0 .1rem; opacity: .85; transition: background-color .15s ease, opacity .15s ease; }
    .navbar-nav .nav-link:hover { background: rgba(255,255,255,.1); opacity: 1; }
    .navbar-nav .nav-link.active { background: rgba(255,255,255,.18); opacity: 1; }
    .navbar-text { font-weight: 600; }
    .brand-logo { height: 36px; width: auto; display: block; }
    .auth-logo { height: 96px; width: auto; display: block; margin: 0 auto 1rem; }
    .hero-logo { height: 132px; width: auto; display: block; margin: 0 auto .5rem; }
    .card { background: var(--surface); border: 1px solid var(--garis); border-radius: var(--radius); box-shadow: var(--shadow-soft); }
    .card.shadow-sm { box-shadow: var(--shadow-soft) !important; }
    .card-soal { border-left: 5px solid var(--brand-primary); }
    .card[class*="text-white"] { border: 0; }
    .icon-circle {
        width: 60px; height: 60px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem; font-size: 1.6rem; color: #fff;
        background: var(--brand-primary);
    }
    .icon-circle.icon-circle-success { background: var(--brand-success); }
    .auth-card { border-radius: 14px; border-top: 5px solid var(--brand-primary); overflow: hidden; box-shadow: var(--shadow-soft-lg); }
    .auth-card:has(.icon-circle-success) { border-top-color: var(--brand-success); }
    .auth-card .card-body { padding: 2.25rem 2rem; }

    .btn { border-radius: 8px; font-weight: 600; padding-top: .55rem; padding-bottom: .55rem; box-shadow: none !important; transition: background-color .15s ease, border-color .15s ease, color .15s ease; }
    .btn-primary { --bs-btn-bg: #0b6e3a; --bs-btn-border-color: #0b6e3a; --bs-btn-hover-bg: #07502a; --bs-btn-hover-border-color: #07502a; --bs-btn-active-bg: #054020; --bs-btn-active-border-color: #054020; --bs-btn-disabled-bg: #0b6e3a; --bs-btn-disabled-border-color: #0b6e3a; }
    .btn-success { --bs-btn-bg: #2d8a4e; --bs-btn-border-color: #2d8a4e; --bs-btn-hover-bg: #226b3c; --bs-btn-hover-border-color: #226b3c; --bs-btn-active-bg: #1a5530; --bs-btn-active-border-color: #1a5530; --bs-btn-disabled-bg: #2d8a4e; --bs-btn-disabled-border-color: #2d8a4e; }
    .btn-warning { --bs-btn-bg: #e3b800; --bs-btn-border-color: #e3b800; --bs-btn-color: #1d2733; --bs-btn-hover-bg: #c9a200; --bs-btn-hover-border-color: #c9a200; --bs-btn-hover-color: #1d2733; --bs-btn-active-bg: #b08d00; --bs-btn-active-border-color: #b08d00; --bs-btn-active-color: #1d2733; }
    .btn-danger { --bs-btn-bg: #a9423c; --bs-btn-border-color: #a9423c; --bs-btn-hover-bg: #8d3732; --bs-btn-hover-border-color: #8d3732; --bs-btn-active-bg: #772e2a; --bs-btn-active-border-color: #772e2a; }
    .btn-outline-primary { --bs-btn-color: #0b6e3a; --bs-btn-border-color: #0b6e3a; --bs-btn-hover-bg: #0b6e3a; --bs-btn-hover-border-color: #0b6e3a; --bs-btn-active-bg: #07502a; --bs-btn-active-border-color: #07502a; }
    .btn-outline-success { --bs-btn-color: #2d8a4e; --bs-btn-border-color: #2d8a4e; --bs-btn-hover-bg: #2d8a4e; --bs-btn-hover-border-color: #2d8a4e; --bs-btn-active-bg: #226b3c; --bs-btn-active-border-color: #226b3c; }
    .btn-outline-danger { --bs-btn-color: #a9423c; --bs-btn-border-color: #a9423c; --bs-btn-hover-bg: #a9423c; --bs-btn-hover-border-color: #a9423c; --bs-btn-active-bg: #8d3732; --bs-btn-active-border-color: #8d3732; }
    .btn-outline-secondary { --bs-btn-color: #4b5663; --bs-btn-border-color: #aab3bd; --bs-btn-hover-bg: #4b5663; --bs-btn-hover-border-color: #4b5663; --bs-btn-active-bg: #39424d; --bs-btn-active-border-color: #39424d; }
    .btn-outline-primary, .btn-outline-secondary, .btn-outline-success, .btn-outline-danger { border-width: 1.5px; }
    .btn-lg { border-radius: 9px; }
    .btn-sm { min-height: 32px; border-radius: 7px; }
    .btn:focus-visible, .form-control:focus-visible, .form-select:focus-visible, .nav-link:focus-visible, a:focus-visible { outline: 3px solid rgba(11,110,58,.6); outline-offset: 2px; }

    .badge { font-weight: 600; border-radius: 6px; padding: .42em .65em; }
    .alert { border: 1px solid transparent; border-left-width: 5px; border-radius: 8px; }
    .alert-success { background: #e3f1e8; border-color: #bfdcc9; border-left-color: var(--brand-success); color: #1d4a2f; }
    .alert-danger  { background: #f6e8e6; border-color: #e6c8c5; border-left-color: var(--brand-danger); color: #66231f; }
    .alert-warning { background: #fbf3cf; border-color: #ecd98a; border-left-color: var(--brand-ochre); color: #5a4700; }
    .alert-info    { background: #e0eef1; border-color: #bcd8de; border-left-color: #2b7a8c; color: #1d4650; }
    .table { --bs-table-bg: transparent; }
    .table thead.table-light th { background: #e1ebe4; color: #2d3946; font-weight: 700; font-size: .88rem; border-bottom: 1px solid var(--garis); }
    .table > :not(caption) > * > * { padding-top: .8rem; padding-bottom: .8rem; border-bottom-color: #e4e8ec; }
    .table-hover > tbody > tr:hover > * { background-color: #eff6f1; }
    .card > .table-responsive, .card > .table { background: var(--surface); }

    .opsi-jawaban label { display: block; padding: .7rem 1rem; border: 1.5px solid var(--garis); border-radius: 8px; margin-bottom: .5rem; cursor: pointer; background: var(--surface); transition: border-color .15s ease, background-color .15s ease; }
    .opsi-jawaban label:hover { border-color: #8fbfa0; background: #f2f8f4; }
    .opsi-jawaban input[type=radio] { margin-right: .5rem; accent-color: var(--brand-primary); }
    .opsi-jawaban input[type=radio]:checked + span { font-weight: 700; }
    .opsi-jawaban label:has(input:checked) { background: var(--brand-primary-tint); border-color: var(--brand-primary); }
    #timer { font-weight: 800; font-size: 1.15rem; font-variant-numeric: tabular-nums; }
    .nav-soal-btn.active-soal { outline-color: var(--brand-ochre) !important; }
    .bg-warning, .badge.bg-warning, .bg-warning.text-white { color: #1d2733 !important; }
    .sticky-top.bg-white { border: 1px solid var(--garis); }

    footer { margin-top: auto; padding: 1.75rem 0; text-align: center; color: var(--ink-soft); font-size: .88rem; }

    .form-control, .form-select, textarea, input[type="text"], input[type="password"], input[type="number"] {
        font-size: 16px; /* cegah zoom otomatis di iPhone */
        border-radius: 8px;
        border: 1.5px solid #c3cbd3;
        background-color: #fff;
    }
    .form-control:focus, .form-select:focus { border-color: var(--brand-primary); box-shadow: 0 0 0 .2rem rgba(11,110,58,.15); }
    .form-label { font-weight: 600; font-size: .9rem; color: #2d3946; }
    .btn { min-height: 40px; }
    .btn-sm { min-height: 32px; }
   
    @media (max-width: 575.98px) {
        .modal-dialog { margin: .75rem; }
        .container, .container-fluid { padding-left: .85rem; padding-right: .85rem; }
        h3 { font-size: 1.35rem; }
        h4 { font-size: 1.15rem; }
    }
    .modal-content { border: 1px solid var(--garis); border-radius: 12px; box-shadow: var(--shadow-soft-lg); }
    .modal-header, .modal-footer { border-color: #e4e8ec; }
    .table-responsive { -webkit-overflow-scrolling: touch; }
    
    .navbar { animation: turun-masuk .5s cubic-bezier(.2,.8,.2,1) backwards; position: relative; }
    .navbar::after { content: ""; position: absolute; left: 0; right: 0; bottom: -3px; height: 3px; background: var(--brand-ochre); transform-origin: left; animation: garis-lebar .8s .25s cubic-bezier(.2,.8,.2,1) backwards; }
    .navbar { border-bottom-color: transparent; }
    .brand-logo { transition: transform .3s ease; }
    .navbar-brand:hover .brand-logo { transform: rotate(-6deg) scale(1.08); }

    .container > * { animation: naik-masuk .55s cubic-bezier(.2,.8,.2,1) backwards; }
    .container > :nth-child(2) { animation-delay: .06s; }
    .container > :nth-child(3) { animation-delay: .12s; }
    .container > :nth-child(4) { animation-delay: .18s; }
    .container > :nth-child(5) { animation-delay: .24s; }
    .container > :nth-child(n+6) { animation-delay: .3s; }
    .container > .row[class*="g-"] { animation: none; }          
    .row[class*="g-"] > [class*="col"] { animation: naik-masuk .55s cubic-bezier(.2,.8,.2,1) backwards; }
    .row[class*="g-"] > :nth-child(1) { animation-delay: .08s; }
    .row[class*="g-"] > :nth-child(2) { animation-delay: .16s; }
    .row[class*="g-"] > :nth-child(3) { animation-delay: .24s; }
    .row[class*="g-"] > :nth-child(4) { animation-delay: .32s; }
    .row[class*="g-"] > :nth-child(n+5) { animation-delay: .4s; }

    .auth-card { animation: zoom-masuk .6s cubic-bezier(.2,.8,.2,1) backwards; }
    .row[class*="g-"] .card { transition: transform .25s ease, box-shadow .25s ease; }
    .row[class*="g-"] .card:hover { transform: translateY(-4px); box-shadow: 0 10px 22px rgba(29,39,51,.12); }
    .card-soal { animation: geser-masuk .45s ease backwards; }

    .auth-logo { animation: zoom-masuk .7s .1s cubic-bezier(.2,.8,.2,1) backwards; }
    .hero-logo { animation: zoom-masuk .7s cubic-bezier(.2,.8,.2,1) backwards, melayang 6s 1s ease-in-out infinite; }

    .btn { transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease; }
    .btn:not(:disabled):not(.disabled):hover { transform: translateY(-2px); }
    .btn:not(:disabled):not(.disabled):active { transform: translateY(0) scale(.97); }
    .btn i { transition: transform .2s ease; }
    .btn:hover i { transform: scale(1.18); }
    .navbar .btn:hover { transform: none; }

    .table tbody tr { animation: baris-masuk .4s ease backwards; }
    .table tbody tr:nth-child(1)  { animation-delay: .20s; }
    .table tbody tr:nth-child(2)  { animation-delay: .24s; }
    .table tbody tr:nth-child(3)  { animation-delay: .28s; }
    .table tbody tr:nth-child(4)  { animation-delay: .32s; }
    .table tbody tr:nth-child(5)  { animation-delay: .36s; }
    .table tbody tr:nth-child(6)  { animation-delay: .40s; }
    .table tbody tr:nth-child(7)  { animation-delay: .44s; }
    .table tbody tr:nth-child(8)  { animation-delay: .48s; }
    .table tbody tr:nth-child(9)  { animation-delay: .52s; }
    .table tbody tr:nth-child(10) { animation-delay: .56s; }
    .table tbody tr:nth-child(n+11) { animation-delay: .6s; }
    
    .modal.fade .modal-dialog { transform: translateY(16px) scale(.95); transition: transform .28s cubic-bezier(.2,.8,.2,1), opacity .2s ease; opacity: 0; }
    .modal.show .modal-dialog { transform: none; opacity: 1; }

    .opsi-jawaban label:has(input:checked) { animation: pilih .3s ease; }
    .opsi-jawaban label { transition: border-color .15s ease, background-color .15s ease, transform .15s ease; }
    .opsi-jawaban label:hover { transform: translateX(3px); }

    .dropdown-menu { animation: zoom-masuk .2s ease backwards; }
    a:not(.btn):not(.nav-link):not(.navbar-brand) { transition: color .15s ease; }</style>
</head>
<body>

