import 'package:flutter/material.dart';
import 'dart:io';

import 'package:webview_flutter/webview_flutter.dart';
import 'package:lottie/lottie.dart';

class home extends StatefulWidget {
  const home({Key? key}) : super(key: key);

  @override
  State<home> createState() => _homeState();
}

class _homeState extends State<home> {
  var pageerror = false;
  late WebViewController controller;
  @override
  void initState() {
    super.initState();
    // Enable virtual display.
    if (Platform.isAndroid) WebView.platform = AndroidWebView();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        return false;
      },
      child: Scaffold(
        body: Padding(
            padding:
                EdgeInsets.only(top: MediaQuery.of(context).size.width / 12),
            child: pageerror == false
                ? WebView(
                    onWebResourceError: (error) {
                      setState(() {
                        pageerror = true;
                      });
                    },
                    initialUrl: 'https://gropart.com',
                    javascriptMode: JavascriptMode.unrestricted,
                  )
                : Center(
                    child: Column(
                      children: [
                        Lottie.asset(
                          'assets/nointernet.json',
                          width: MediaQuery.of(context).size.width * 0.8,
                          height: MediaQuery.of(context).size.height * 0.8,
                        ),
                        GestureDetector(
                          onTap: () {
                            Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (context) => home()));
                          },
                          child: Padding(
                            padding: const EdgeInsets.all(8.0),
                            child: Text("Retry"),
                          ),
                        )
                      ],
                    ),
                  )),
      ),
    );
  }
}
