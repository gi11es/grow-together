/*
 * Kindly provided by Mark Gibbons
 * "no error handling, but it works"
 */
 
protected void Page_Load(object sender, EventArgs e)
{

	WebRequest webRequest = WebRequest.Create(string.Format("http://grow.darumazone.com/serve2.php?growthid={0}&quantity=10&format=xml",
"[insert your growth id here]"));

	XPathDocument doc = new XPathDocument(webRequest.GetResponse().GetResponseStream());
	XPathNavigator nav = doc.CreateNavigator();

	if (nav.SelectSingleNode("//growth/result").ValueAsInt == 0)
	{
		foreach (XPathNavigator node in nav.Select("//growth/app"))
		{
			AddApp(node);
		}
	}
}

private void AddApp(XPathNavigator node)
{
	Literal lit = new Literal();
	lit.Text = string.Format("<div class='coolappsdiv'><img src='{1}'/> <a href='{2}'>{0}</a> - {3}</div>",
	node.SelectSingleNode("name"),
	node.SelectSingleNode("icon"),
	node.SelectSingleNode("link"),
	node.SelectSingleNode("text")
);

// ads is a div server control
ads.Controls.Add(lit);
}
