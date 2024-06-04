import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:gropart/home.dart';
import 'package:gropart/onboardscreens/onboard.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<bool>(
      future: isFirstTime(),
      builder: (BuildContext context, AsyncSnapshot<bool> snapshot) {
        if (snapshot.hasData) {
          final bool isFirstTime = snapshot.data!;
          return MaterialApp(
            debugShowCheckedModeBanner: false,
            home: isFirstTime ? OnBoardScreen() : home(),
          );
        }
        return const CircularProgressIndicator();
      },
    );
  }

  Future<bool> isFirstTime() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    bool isFirstTime = prefs.getBool('isFirstTime') ?? true;
    print("------------------$isFirstTime");
    // if (isFirstTime) {
    //   await prefs.setBool('isFirstTime', false);
    // }
    return isFirstTime;
  }
}
