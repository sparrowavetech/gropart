import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:gropart/home.dart';
import 'package:gropart/onboardscreens/onboard_contents.dart';

class OnBoardScreen extends StatefulWidget {
  const OnBoardScreen({Key? key}) : super(key: key);

  @override
  State<OnBoardScreen> createState() => _OnBoardScreenState();
}

class _OnBoardScreenState extends State<OnBoardScreen> {
  bool loader = false;
  final _controller = PageController();
  int _currentPage = 0;
  bool showOnboarding = true; // Variable to track checkbox state

  AnimatedContainer _buildDots({int? index}) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.all(
          Radius.circular(50),
        ),
        color: const Color(0xFF000000),
      ),
      margin: const EdgeInsets.only(right: 5),
      height: 10,
      curve: Curves.easeIn,
      width: _currentPage == index ? 20 : 10,
    );
  }

  @override
  Widget build(BuildContext context) {
    final height = MediaQuery.of(context).size.height;
    final width = MediaQuery.of(context).size.width;
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              flex: 3,
              child: PageView.builder(
                controller: _controller,
                onPageChanged: (value) => setState(() => _currentPage = value),
                itemCount: 3,
                itemBuilder: (context, i) {
                  return Container(
                    // color: colors[i],
                    child: Padding(
                      padding: const EdgeInsets.all(40.0),
                      child: Column(
                        children: [
                          SizedBox(
                            height: (height >= 840) ? 40 : 40,
                          ),
                          Image.asset(
                            contents[i].image!,
                            height: MediaQuery.of(context).size.height * 0.32,
                          ),
                          SizedBox(
                            height: (height >= 840) ? 20 : 20,
                          ),
                          Text(
                            contents[i].title!,
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontFamily: "Mulish",
                              fontWeight: FontWeight.w600,
                              fontSize: (width <= 550) ? 18 : 25,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
            Expanded(
              flex: 2,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(
                      3,
                      (int index) => _buildDots(index: index),
                    ),
                  ),
                  _currentPage + 1 == 3
                      ? Padding(
                          padding: const EdgeInsets.all(10),
                          child: loader == false
                              ? Column(
                                  children: [
                                    CheckboxListTile(
                                      title: Text("Don't show this again"),
                                      value: showOnboarding,
                                      onChanged: (value) {
                                        setState(() {
                                          showOnboarding = value!;
                                          print("checkbox");
                                          print(
                                              "------------------$showOnboarding");
                                        });
                                      },
                                    ),
                                    ElevatedButton(
                                      onPressed: () {
                                        _setFirstTimePreference(showOnboarding);
                                        setState(() {
                                          loader = true;
                                        });
                                        Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                                builder: (context) => home()));
                                      },
                                      child: Text("CONTINUE"),
                                      style: ElevatedButton.styleFrom(
                                        primary: Colors.black,
                                        shape: new RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(50),
                                        ),
                                        padding: (width <= 550)
                                            ? EdgeInsets.symmetric(
                                                horizontal: 100, vertical: 20)
                                            : EdgeInsets.symmetric(
                                                horizontal: width * 0.2,
                                                vertical: 25),
                                        textStyle: TextStyle(
                                            fontSize: (width <= 550) ? 13 : 17),
                                      ),
                                    ),
                                  ],
                                )
                              : Text(
                                  "Loading...",
                                  style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold),
                                ))
                      : Padding(
                          padding: const EdgeInsets.all(30),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              TextButton(
                                onPressed: () {
                                  _controller.jumpToPage(2);
                                },
                                child: Text(
                                  "SKIP",
                                  style: TextStyle(color: Colors.black),
                                ),
                                style: TextButton.styleFrom(
                                  elevation: 0,
                                  textStyle: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: (width <= 550) ? 13 : 17,
                                  ),
                                ),
                              ),
                              ElevatedButton(
                                onPressed: () {
                                  _controller.nextPage(
                                    duration: Duration(milliseconds: 200),
                                    curve: Curves.easeIn,
                                  );
                                },
                                child: Text("NEXT"),
                                style: ElevatedButton.styleFrom(
                                  primary: Colors.black,
                                  shape: new RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(50),
                                  ),
                                  elevation: 0,
                                  padding: (width <= 550)
                                      ? EdgeInsets.symmetric(
                                          horizontal: 30, vertical: 20)
                                      : EdgeInsets.symmetric(
                                          horizontal: 30, vertical: 25),
                                  textStyle: TextStyle(
                                      fontSize: (width <= 550) ? 13 : 17),
                                ),
                              ),
                            ],
                          ),
                        )
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _setFirstTimePreference(bool value) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    if (value) {
      await prefs.setBool('isFirstTime', false);
    } else {
      await prefs.setBool('isFirstTime', true);
    }
    print("setpref");
    print("------------------$value");
  }
}
