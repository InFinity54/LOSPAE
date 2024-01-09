using LOSPAÉ.ViewModels;

using Microsoft.UI.Xaml.Controls;

namespace LOSPAÉ.Views;

public sealed partial class HistoryPage : Page
{
    public HistoryViewModel ViewModel
    {
        get;
    }

    public HistoryPage()
    {
        ViewModel = App.GetService<HistoryViewModel>();
        InitializeComponent();
    }
}
