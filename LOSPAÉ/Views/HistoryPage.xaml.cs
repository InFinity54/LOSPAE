using LOSPAÉ.ViewModels;

using Microsoft.UI.Xaml.Controls;

namespace LOSPAÉ.Views;

// TODO: Change the grid as appropriate for your app. Adjust the column definitions on DataGridPage.xaml.
// For more details, see the documentation at https://docs.microsoft.com/windows/communitytoolkit/controls/datagrid.
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
