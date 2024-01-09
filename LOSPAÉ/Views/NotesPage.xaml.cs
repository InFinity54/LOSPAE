using LOSPAÉ.ViewModels;

using Microsoft.UI.Xaml.Controls;

namespace LOSPAÉ.Views;

public sealed partial class NotesPage : Page
{
    public NotesViewModel ViewModel
    {
        get;
    }

    public NotesPage()
    {
        ViewModel = App.GetService<NotesViewModel>();
        InitializeComponent();
    }
}
