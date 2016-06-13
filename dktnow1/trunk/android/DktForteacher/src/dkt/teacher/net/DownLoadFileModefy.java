package dkt.teacher.net;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;

import dkt.teacher.MyContants;
import android.os.Message;
import android.widget.Toast;

/**
 * 用户下载文件的功能类
 * @author Administrator
 *
 */
public class DownLoadFileModefy {
	private String savename;
	private MyThread thread;
	private  DownLoadHandler handler;
	private String strurl;
	public boolean interceptFlag = false;
	private int num;
	
	/**
	 * 构造函数
	 * @param strurl 用户请求url 如 /server/download(不含http://127.0.0.1)
	 * @param handler
	 * @param params 请求参数名 
	 * @param values 请求参数值
	 * @param dbname 上传文件名 如 12345.db
	 */
	public DownLoadFileModefy(String strurl, DownLoadHandler handler, String savename, int num){
		
		this.savename = savename;
		this.handler = handler;
		this.strurl = strurl;
		this.num = num;
		doThread();
	}
	/**
	 * 异步线程处理http 请求
	 */
	public void doThread() {
		thread = new MyThread();
		thread.start();

	}
	
	class MyThread extends Thread {

		@Override
		public void run() {
			downFile();
		}

	}
	
	private void downFile(){
		
		GetIp getip = new GetIp(handler.context);
		String service_ip = getip.servise_ip;
		if(service_ip.equals("")){
			sendMsg(MyContants.HTTP_URL_NULL_WRONG, 0);
			return;
		}
		InputStream is = null;
		FileOutputStream fos = null;
		String savePath = "/sdcard/Dkt/Resource/"+savename;
		File DbFile = new File(savePath);
		String fileDownUrl = MyContants.HTTP_PREFIX+service_ip + strurl;
		try {
			URL url = new URL(fileDownUrl);

			HttpURLConnection conn = (HttpURLConnection) url
					.openConnection();
			conn.connect();
			int length = conn.getContentLength();
			is = conn.getInputStream();

//			File file = new File(path);
//			if (!file.exists()) {
//				file.mkdir();
//			}
			
			fos = new FileOutputStream(DbFile);

			int count = 0;
			byte buf[] = new byte[1024];

			do {
				int numread = is.read(buf);
				count += numread;
				int  progress = (int) (((float) count / length) * 100) ;
				// 更新进度
				sendMsg(MyContants.HTTP_DOWNLOAD,progress);
				if (numread <= 0) {
					// 下载完成
					sendMsg(MyContants.HTTP_DOWNLOAD_SUCESS,num);
					break;
				}
				fos.write(buf, 0, numread);
			} while (!interceptFlag);// 点击取消就停止下载.

			fos.close();
			is.close();
		} catch (MalformedURLException e) {
			DbFile.delete();
			System.out.println("dbFile======2=============");
			e.printStackTrace();
			sendMsg(MyContants.HTTP_DOWNLOAD_FAIL,0);
		} catch (IOException e) {
			DbFile.delete();
			System.out.println("dbFile======3=============");
			e.printStackTrace();
			sendMsg(MyContants.HTTP_DOWNLOAD_FAIL,0);
		} finally {
			if(null != fos) {
				try {
					fos.close();
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
			if(null != is) {
				try {
					is.close();
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
	}
	
	private void sendMsg(int errorCode,int str){

		Message msg = handler.obtainMessage();
		msg.what = errorCode;
		msg.obj = str;
		handler.sendMessage(msg);
		
	}
	

}